<?php

namespace Point\PointPurchasing\Helpers\FixedAssets;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Core\Models\Setting;
use Point\Core\Models\Vesa;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceived;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceivedDetail;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder;

class FixedAssetsGoodsReceivedHelper
{
    public static function searchList($list_goods_received, $status, $date_from, $date_to, $search)
    {
        $list_goods_received = $list_goods_received->where('form_status', '=', $status ? : 0);
        if ($date_from) {
            $list_goods_received = $list_goods_received->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_goods_received = $list_goods_received->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_goods_received = $list_goods_received->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%');
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
        $goods_received = new FixedAssetsGoodsReceived;
        $goods_received->formulir_id = $formulir->id;
        $goods_received->warehouse_id = $request->input('warehouse_id');
        $goods_received->supplier_id = $reference->supplier_id;
        $goods_received->driver = $request->input('driver');
        $goods_received->license_plate = $request->input('license_plate');
        $goods_received->fixed_assets_order_id = $request->input('reference_purchase_order_id');
        $goods_received->include_expedition = $request->input('include_expedition') ? 1 : 0;
        $goods_received->expedition_fee = number_format_db($request->input('expedition_fee'));
        $goods_received->save();

        $goods_received->formulir->approval_status = 1;
        $goods_received->formulir->approval_to = 1;
        $goods_received->formulir->approval_at = \Carbon::now();
        $goods_received->formulir->save();

        for ($i=0 ; $i<count($request->input('name')) ; $i++) {
            if (number_format_db($request->input('item_quantity')[$i]) > number_format_db($request->input('item_quantity_reference')[$i])) {
                throw new PointException('Your Goods Received quantity not matched');
            }

            $goods_received_item = new FixedAssetsGoodsReceivedDetail;
            $goods_received_item->fixed_assets_goods_received_id = $goods_received->id;
            $goods_received_item->coa_id = $request->input('coa_id')[$i];
            $goods_received_item->name = $request->input('name')[$i];
            $goods_received_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $goods_received_item->price = number_format_db($request->input('item_price')[$i]);
            $goods_received_item->discount = number_format_db($request->input('item_discount')[$i]);
            $goods_received_item->unit = $request->input('item_unit_name')[$i];
            $goods_received_item->allocation_id = 1;
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
        if ($request->input('reference_expedition_order_id') != '') {
            $expedition_order = ExpeditionOrder::find($request->input('reference_expedition_order_id'));
            formulir_lock($expedition_order->formulir_id, $goods_received->formulir_id);
        } else {
            formulir_lock($reference->formulir_id, $goods_received->formulir_id);
        }
        // update status reference
        self::updateStatusReference($request, $reference);

        return $goods_received;
    }

    public static function updateStatusReference($request, $reference)
    {
        // update by remaining quantity
        $reference_purchase_order = FixedAssetsPurchaseOrder::find($request->input('reference_purchase_order_id'));
        $close = false;
        if ($reference_purchase_order->createGoodsReceiveFromExpedition() === false) {
            foreach ($reference->details as $reference_item) {
                $remaining_quantity = ReferHelper::remaining(get_class($reference_item), $reference_item->id, $reference_item->quantity);
                $close = false;
                if ($remaining_quantity == 0) {
                    $close = true;
                }
            }
        }
        // update by form close manual
        if ($request->input('close') != null || $close === true) {
            $reference->formulir->form_status = 1;
        }

        $reference->formulir->save();
    }
}
