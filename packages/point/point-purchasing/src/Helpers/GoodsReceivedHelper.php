<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\PointExpedition\Helpers\ExpeditionOrderHelper;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\GoodsReceivedItem;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;
use Point\PointPurchasing\Models\Inventory\PurchaseOrderItem;

class GoodsReceivedHelper
{
    public static function searchList($list_goods_received, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($order_by) {
            $list_goods_received = $list_goods_received->orderBy($order_by, $order_type);
        } else {
            $list_goods_received = $list_goods_received->orderByStandard();
        }

        if ($status != 'all') {
            $list_goods_received = $list_goods_received->where('formulir.form_status', '=', $status ?: 0);
        }

        if ($date_from) {
            $list_goods_received = $list_goods_received->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_goods_received = $list_goods_received->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_goods_received = $list_goods_received->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                    ->orWhere('person.name', 'like', '%'.$search.'%');
            });
        }

        return $list_goods_received;
    }

    private static function getReference($request)
    {
        $reference_type = $request->input('reference_purchase_order');
        $reference_id = $request->input('reference_purchase_order_id');
        return $reference_type::find($reference_id);
    }

    public static function create(Request $request, $formulir)
    {
        $reference = self::getReference($request);
        $goods_received = new GoodsReceived;
        $goods_received->formulir_id = $formulir->id;
        $goods_received->warehouse_id = $request->input('warehouse_id');
        $goods_received->supplier_id = $reference->supplier_id;
        $goods_received->driver = $request->input('driver');
        $goods_received->license_plate = $request->input('license_plate');
        $goods_received->point_purchasing_order_id = $request->input('reference_purchase_order_id');
        $goods_received->include_expedition = $request->input('include_expedition') ? 1 : 0;
        $goods_received->expedition_fee = number_format_db($request->input('expedition_fee'));
        $goods_received->save();

        $goods_received->formulir->save();

        for ($i=0 ; $i<count($request->input('item_id')) ; $i++) {
            if (number_format_db($request->input('item_quantity')[$i]) > number_format_db($request->input('item_quantity_reference')[$i])) {
                throw new PointException('Your Goods Received quantity not matched');
            }

            $goods_received_item = new GoodsReceivedItem;
            $goods_received_item->point_purchasing_goods_received_id = $goods_received->id;
            $goods_received_item->item_id = $request->input('item_id')[$i];
            $goods_received_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $goods_received_item->price = number_format_db($request->input('item_price')[$i]);
            $goods_received_item->discount = number_format_db($request->input('item_discount')[$i]);
            $goods_received_item->unit = $request->input('item_unit_name')[$i];
            $goods_received_item->converter = number_format_db($request->input('item_unit_converter')[$i]);
            $goods_received_item->allocation_id = $request->input('allocation_id')[$i] ? $request->input('allocation_id')[$i] : 1;
            $goods_received_item->save();
            ReferHelper::create(
                $request->input('reference_item_type')[$i],
                $request->input('reference_item_id')[$i],
                get_class($goods_received_item),
                $goods_received_item->id,
                get_class($goods_received),
                $goods_received->id,
                $goods_received_item->quantity
            );
        }

        /**
         * Locking process
         * - when reference from expedition order, locked_id = expedition formulir
         * - when reference from purchase order, locked_id = purchasing formulir
         */
        if ($request->input('group_expedition_order') != '') {
            $list_expedition_order = ExpeditionOrder::joinFormulir()->approvalApproved()->notArchived()->selectOriginal()->where('done', 0)->where('form_reference_id', $reference->formulir_id)->where('group', $request->input('group_expedition_order'))->get();
            foreach ($list_expedition_order as $expedition_order) {
                formulir_lock($expedition_order->formulir_id, $goods_received->formulir_id);
                $expedition_order->done = 1;
                $expedition_order->save();
            }
        } else {
            formulir_lock($reference->formulir_id, $goods_received->formulir_id);
        }


        // update status reference
        self::updateStatusReference($request, $reference);

        // update expedition reference
        if ($request->input('reference_expedition_order_id')) {
            self::updateExpeditionReference($request->input('reference_expedition_order_id'));
        }

        return $goods_received;
    }

    public static function updateStatusReference($request, $reference)
    {
        // update by remaining quantity
        $close = false;
        foreach ($reference->items as $reference_item) {
            $remaining_quantity = ReferHelper::remaining(get_class($reference_item), $reference_item->id, $reference_item->quantity);
            $close = false;
            if ($remaining_quantity == 0) {
                $close = true;
            }
        }
        // update by form close manual
        if ($request->input('close') != null || $close === true) {
            $reference->formulir->form_status = 1;
        }

        $reference->formulir->save();
    }

    public static function updateExpeditionReference($expedition_order_id)
    {
        $expedition_order = ExpeditionOrder::find($expedition_order_id);
        $purchase_order = PurchaseOrder::where('formulir_id', $expedition_order->form_reference_id)->first();
        $finish = false;
        foreach ($expedition_order->items as $expedition_order_item) {
            $purchase_order_item = PurchaseOrderItem::where('point_purchasing_order_id', $purchase_order->id)->where('item_id', $expedition_order_item->item_id)->first();
            $remaining = ReferHelper::remaining(get_class($purchase_order_item), $purchase_order_item->id, $purchase_order_item->quantity);
            if ($remaining == 0) {
                $finish = true;
            }
        }
        if ($finish && !$expedition_order->include_expedition) {
            $expedition_order_reference = ExpeditionOrderReference::where('expedition_reference_id', $expedition_order->form_reference_id)->first();
            $expedition_order_reference->finish =  1;
            $expedition_order_reference->save();
        }
    }

    public static function undoneExpedition($goods_received)
    {
        $formulir_locks = FormulirLock::where('locking_id', '=', $goods_received->formulir_id)->get();
        foreach ($formulir_locks as $formulir_lock) {
            $formulir = Formulir::find($formulir_lock->locked_id);
            if ($formulir->formulirable_type == get_class(new ExpeditionOrder())) {
                $expedition_order = ExpeditionOrder::find($formulir->formulirable_id);
                $expedition_order->done = 0;
                $expedition_order->save();
            }
        }
    }
}
