<?php

namespace Point\PointSales\Helpers;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\ReferHelper;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\PointSales\Models\Sales\DeliveryOrderItem;
use Point\PointSales\Models\Sales\SalesOrder;

class DeliveryOrderHelper
{
    public static function searchList($list_delivery_order, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_delivery_order = $list_delivery_order->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $list_delivery_order = $list_delivery_order->orderBy($order_by, $order_type);
        } else {
            $list_delivery_order = $list_delivery_order->orderByStandard();
        }

        if ($date_from) {
            $list_delivery_order = $list_delivery_order->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_delivery_order = $list_delivery_order->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_delivery_order = $list_delivery_order->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_delivery_order;
    }

    private static function getReference($request)
    {
        $reference_type = $request->input('reference_sales_order');
        $reference_id = $request->input('reference_sales_order_id');
        return $reference_type::find($reference_id);
    }

    private static function updateStatusReference($request, $reference)
    {
        // update by remaining quantity
        $reference_sales_order = SalesOrder::find($request->input('reference_sales_order_id'));
        $close = true;

        if ($reference_sales_order->createDeliveryFromExpedition() === false) {
            foreach ($reference->items as $reference_item) {
                $remaining_quantity = ReferHelper::remaining(get_class($reference_item), $reference_item->id, $reference_item->quantity);

                // if one item have quantity then break the operation
                if ($remaining_quantity > 0) {
                    return null;
                }
            }
        }

        // close reference form if meet one condition
        // 1. user check 'close checkbox'
        // 2. all item have been withdrawn
        if ($request->input('close') != null || $close === true) {
            $reference->formulir->form_status = 1;
            $reference->formulir->save();
        }
    }

    public static function create(Request $request, $formulir)
    {
        $reference = self::getReference($request);
        $delivery_order = new DeliveryOrder;
        $delivery_order->formulir_id = $formulir->id;
        $delivery_order->warehouse_id = $request->input('warehouse_id');
        $delivery_order->person_id = $reference->person_id;
        $delivery_order->driver = $request->input('driver');
        $delivery_order->license_plate = $request->input('license_plate');
        $delivery_order->point_sales_order_id = $request->input('reference_sales_order_id');
        $delivery_order->include_expedition = $request->input('include_expedition') ? 1 : 0;
        $delivery_order->expedition_fee = number_format_db($request->input('expedition_fee'));
        $delivery_order->save();

        if ($reference->is_cash) {
            if (number_format_db($request->get('value_deliver')) < $request->get('dp_amount')) {
                $delivery_order->formulir->approval_status = 1;
                $delivery_order->formulir->approval_to = 1;
                $delivery_order->formulir->approval_at = \Carbon::now();
            }
        }

        $delivery_order->formulir->approval_status = 1;
        $delivery_order->formulir->approval_to = 1;
        $delivery_order->formulir->approval_at = \Carbon::now();

        $delivery_order->formulir->save();

        for ($i=0 ; $i<count($request->input('item_id')) ; $i++) {
            if (number_format_db($request->input('item_quantity')[$i]) > number_format_db($request->input('item_quantity_reference')[$i])) {
                throw new PointException('Your delivery quantity not matched');
            }

            $delivery_order_item = new DeliveryOrderItem;
            $delivery_order_item->point_sales_delivery_order_id = $delivery_order->id;
            $delivery_order_item->item_id = $request->input('item_id')[$i];
            $delivery_order_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $delivery_order_item->price = number_format_db($request->input('item_price')[$i]);
            $delivery_order_item->discount = number_format_db($request->input('item_discount')[$i]);
            $delivery_order_item->unit = $request->input('item_unit_name')[$i];
            $delivery_order_item->allocation_id = $request->input('allocation_id')[$i] ?: 1;
            $delivery_order_item->converter = number_format_db($request->input('item_unit_converter')[$i]);
            $delivery_order_item->save();

            ReferHelper::create(
                $request->input('reference_item_type')[$i],
                $request->input('reference_item_id')[$i],
                get_class($delivery_order_item),
                $delivery_order_item->id,
                get_class($delivery_order),
                $delivery_order->id,
                $delivery_order_item->quantity
            );
        }

        /**
         * Locking process
         * - when reference from expedition order, locked_id = expedition formulir
         * - when reference from sales order, locked_id = sales formulir
         */
        if ($request->input('reference_expedition_order_id') != '') {
            $expedition_order = ExpeditionOrder::find($request->input('reference_expedition_order_id'));
            formulir_lock($expedition_order->formulir_id, $delivery_order->formulir_id);
        } else {
            formulir_lock($reference->formulir_id, $delivery_order->formulir_id);
        }

        // update status reference
        self::updateStatusReference($request, $reference);

        return $delivery_order;
    }
}
