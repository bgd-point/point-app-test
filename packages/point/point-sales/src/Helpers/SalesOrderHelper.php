<?php

namespace Point\PointSales\Helpers;

use Illuminate\Http\Request;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointExpedition\Models\ExpeditionOrderReferenceItem;
use Point\PointSales\Models\SalesQuotationItem;
use Point\PointSales\Models\Sales\SalesOrder;
use Point\PointSales\Models\Sales\SalesOrderItem;

class SalesOrderHelper
{
    public static function searchList($list_sales_order, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_sales_order = $list_sales_order->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $list_sales_order = $list_sales_order->orderBy($order_by, $order_type);
        } else {
            $list_sales_order = $list_sales_order->orderByStandard();
        }

        if ($date_from) {
            $list_sales_order = $list_sales_order->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_sales_order = $list_sales_order->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_sales_order = $list_sales_order->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_sales_order;
    }

    private static function getIncludeExpedition(Request $request)
    {
        if ($request->input('include_expedition')) {
            return 1;
        }

        return 0;
    }

    private static function getExpeditionFee(Request $request)
    {
        if ($request->input('include_expedition')) {
            return number_format_db($request->input('expedition_fee'));
        }

        return 0;
    }

    private static function getReference($request)
    {
        $reference_type = $request->input('reference_type');
        $reference_id = $request->input('reference_id');
        if ($reference_type != '') {
            $reference = $reference_type::find($reference_id);
            return $reference;
        }

        return null;
    }

    private static function getCashStatus(Request $request)
    {
        if ($request->input('is_cash')) {
            return 1;
        }

        return 0;
    }

    public static function registerToExpedition($sales_order)
    {
        $expedition_order_reference = new ExpeditionOrderReference;
        $expedition_order_reference->expedition_reference_id = $sales_order->formulir_id;
        $expedition_order_reference->person_id = $sales_order->person_id;
        $expedition_order_reference->type_of_tax = $sales_order->type_of_tax;
        $expedition_order_reference->include_expedition = $sales_order->include_expedition;
        $expedition_order_reference->expedition_fee = $sales_order->expedition_fee;
        $expedition_order_reference->subtotal = $sales_order->subtotal;
        $expedition_order_reference->discount = $sales_order->discount;
        $expedition_order_reference->tax_base = $sales_order->tax_base;
        $expedition_order_reference->tax = $sales_order->tax;
        $expedition_order_reference->is_cash = $sales_order->is_cash;
        $expedition_order_reference->total = $sales_order->total;
        $expedition_order_reference->save();

        foreach ($sales_order->items as $item_order) {
            $expedition_item = new ExpeditionOrderReferenceItem;
            $expedition_item->point_expedition_order_reference_id = $expedition_order_reference->id;
            $expedition_item->item_id = $item_order->item_id;
            $expedition_item->quantity = number_format_db($item_order->quantity);
            $expedition_item->price = number_format_db($item_order->price);
            $expedition_item->discount = number_format_db($item_order->discount);
            $expedition_item->unit = $item_order->unit;
            $expedition_item->converter = number_format_db($item_order->converter);
            $expedition_item->allocation_id = 1; // id = 1 is default for no allocation
            $expedition_item->save();
        }
    }

    public static function create(Request $request, $formulir)
    {
        $reference = self::getReference($request);

        $sales_order = new SalesOrder;
        $sales_order->formulir_id = $formulir->id;
        $sales_order->person_id = $request->input('person_id');
        $sales_order->type_of_tax = $request->input('type_of_tax');
        $sales_order->include_expedition = self::getIncludeExpedition($request);
        $sales_order->expedition_fee = self::getExpeditionFee($request);
        $sales_order->is_cash = self::getCashStatus($request);
        $sales_order->save();
         
        $subtotal = 0;
        for ($i=0 ; $i < count($request->input('item_id')) ; $i++) {
            $sales_order_item = new SalesOrderItem;
            $sales_order_item->point_sales_order_id = $sales_order->id;
            $sales_order_item->item_id = $request->input('item_id')[$i];
            $sales_order_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $sales_order_item->price = number_format_db($request->input('item_price')[$i]);
            $sales_order_item->discount = number_format_db($request->input('item_discount')[$i]);
            $sales_order_item->unit = $request->input('item_unit_name')[$i];
            $sales_order_item->allocation_id = $request->input('allocation_id')[$i];
            $sales_order_item->converter = 1;
            $sales_order_item->save();

            $subtotal += ($sales_order_item->quantity * $sales_order_item->price) - ($sales_order_item->quantity * $sales_order_item->price/100 * $sales_order_item->discount);
        }

        $discount = number_format_db($request->input('discount'));
        $tax_base = $subtotal -($subtotal/100 * $discount);
        $tax = 0;

        if ($request->input('type_of_tax') == 'exclude') {
            $tax = $tax_base * 10 / 100;
        }
        if ($request->input('type_of_tax') == 'include') {
            $tax_base =  $tax_base * 100 / 110;
            $tax =  $tax_base * 10 / 100;
        }

        $sales_order->subtotal = $subtotal;
        $sales_order->discount = $discount;
        $sales_order->tax_base = $tax_base;
        $sales_order->tax = $tax;
        $sales_order->total = $tax_base + $tax + $sales_order->expedition_fee;
        $sales_order->save();

        if ($reference != null) {
            formulir_lock($reference->formulir_id, $sales_order->formulir_id);
            $reference->formulir->form_status = 1;
            $reference->formulir->save();
        }

        return $sales_order;
    }
}
