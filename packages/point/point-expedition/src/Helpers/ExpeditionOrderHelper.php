<?php

namespace Point\PointExpedition\Helpers;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Core\Models\Vesa;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointExpedition\Models\ExpeditionOrderReferenceItem;
use Point\PointExpedition\Models\ExpeditionRequisition;
use Point\PointExpedition\Models\PurchaseRequisitionItem;
use Point\PointPurchasing\Models\PurchaseOrder;
use Point\PointSales\Models\Sales\SalesOrder;

class ExpeditionOrderHelper
{
    public static function searchList($list_expedition_order, $order_by, $order_type, $status, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_expedition_order = $list_expedition_order->where('formulir.form_status', '=', $status ? : 0);
        }

        if ($order_by) {
            $list_expedition_order = $list_expedition_order->orderBy($order_type, $order_by);
        } else {
            $list_expedition_order = $list_expedition_order->orderByStandard();
        }

        if ($date_from) {
            $list_expedition_order = $list_expedition_order->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_expedition_order = $list_expedition_order->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_expedition_order = $list_expedition_order->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%' . $search . '%')
                    ->orWhere('formulir.form_number', 'like', '%' . $search . '%');
            });
        }

        return $list_expedition_order;
    }

    public static function create(Request $request, $formulir)
    {
        $reference = self::getReference($request);
        $expedition_order = new ExpeditionOrder;
        $expedition_order->formulir_id = $formulir->id;
        $expedition_order->expedition_id = $request->input('expedition_id');
        $expedition_order->group = self::getGroup($request);
        $expedition_order->form_reference_id = $reference->formulir_id;
        $expedition_order->type_of_fee = '';
        $expedition_order->expedition_fee = number_format_db($request->input('subtotal'));
        $expedition_order->delivery_date = date_format_db($request->input('form_date'), $request->input('time'));
        $expedition_order->type_of_tax = $request->input('type_of_tax');
        $expedition_order->tax_base = number_format_db($request->input('tax_base'));
        $expedition_order->tax = number_format_db($request->input('tax'));
        $expedition_order->discount = number_format_db($request->input('discount'));
        $expedition_order->total = number_format_db($request->input('total'));
        $expedition_order->save();

        for ($i = 0; $i < count($request->input('item_id')); $i++) {
            // validate quantity
            $available_quantity = self::availableQuantity($expedition_order->form_reference_id, $request->input('item_id')[$i]);
            if (! $request->input('group')) {
                if ($request->input('item_quantity')[$i] > $available_quantity) {
                    throw new PointException('QUANTITY OF DELIVERY IS BIGGER THAN AVAILABLE QUANTITY');
                }
            }

            $expedition_order_item = new ExpeditionOrderItem;
            $expedition_order_item->point_expedition_order_id = $expedition_order->id;
            $expedition_order_item->item_id = $request->input('item_id')[$i];
            $expedition_order_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $expedition_order_item->unit = $request->input('item_unit_name')[$i];
            $expedition_order_item->price = $request->input('price')[$i];
            $expedition_order_item->item_fee = 0;
            $expedition_order_item->converter = 1;
            $expedition_order_item->save();

        }

        formulir_lock($reference->formulir_id, $expedition_order->formulir_id);
        return $expedition_order;
    }

    private static function getReference($request)
    {
        $reference_type = $request->input('reference_type');
        $reference_id = $request->input('reference_id');
        return $reference_type::find($reference_id);
    }

    private static function getGroup($request)
    {
        if ($request->input('group')) {
            $expedition_order = ExpeditionOrder::find($request->input('group'));
            return $expedition_order->group;
        }

        $expedition_order = ExpeditionOrder::joinFormulir()->notArchived()->approvalApproved()->where('form_reference_id', $request->input('reference_formulir_id'))->groupBy('group')->max('group');
        return $expedition_order + 1;
    }

    public static function originalQuantityReference($form_reference_id, $item_id)
    {
        /**
         * Get quantity item expedition reference
         */
        $expedition_reference_detail = ExpeditionOrderReferenceItem::join('point_expedition_order_reference', 'point_expedition_order_reference.id', '=', 'point_expedition_order_reference_item.point_expedition_order_reference_id')
            ->where('point_expedition_order_reference.expedition_reference_id', $form_reference_id)
            ->where('item_id', $item_id)
            ->first();
            
        return $expedition_reference_detail->quantity;
    }

    public static function totalQuantityExpeditionItemDelivered($form_reference_id, $item_id)
    {
        /**
         * get quantity expedition order item
         */
        $array_expedition_order_id = ExpeditionOrder::joinFormulir()->notArchived()->approvalApproved()->where('form_reference_id', $form_reference_id)->groupBy('group')->select('point_expedition_order.id')->get()->toArray();
        $quantity_expedition_order_item = ExpeditionOrderItem::whereIn('point_expedition_order_id', $array_expedition_order_id)->where('item_id', $item_id)->groupBy('item_id')->sum('quantity');

        return $quantity_expedition_order_item;
    }

    public static function availableQuantity($form_reference_id, $item_id)
    {
        $difference = self::originalQuantityReference($form_reference_id, $item_id) - self::totalQuantityExpeditionItemDelivered($form_reference_id, $item_id);
        return $difference;
    }
    
    public static function getToExpeditionOrder()
    {
        $list_purchase_order = PurchaseOrder::joinFormulir()->availableToPickup()->selectOriginal()->get()->toArray();
        $list_sales_order = SalesOrder::joinFormulir()->availableToPickup()->selectOriginal()->get()->toArray();

        $expedition_order = [];
        if ($list_purchase_order) {
            array_push($expedition_order, $list_purchase_order);
        }

        if ($list_sales_order) {
            array_push($expedition_order, $list_sales_order);
        }

        return $expedition_order;
    }

    public static function cancelExpeditionReference($formulir_id)
    {
        $formulir = Formulir::find($formulir_id);
        $expedition_reference = ExpeditionOrderReference::where('expedition_order_id', '=', $formulir->formulirable_id)->first();
        if ($expedition_reference) {
            $expedition_reference->expedition_order_id = null;
            $expedition_reference->save();
        }
    }
}
