<?php

namespace Point\PointSales\Helpers;

use Illuminate\Http\Request;
use Point\Core\Models\Vesa;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\ItemUnit;
use Point\PointSales\Models\Sales\SalesQuotation;
use Point\PointSales\Models\Sales\SalesQuotationItem;
use Point\PointSales\Models\Sales\SalesOrder;
use Point\PointSales\Models\Sales\SalesOrderItem;

class SalesQuotationHelper
{
    public static function searchList($list_sales_quotation, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_sales_quotation = $list_sales_quotation->where('formulir.form_status', '=', $status ?: 0);
        }
        if ($order_by) {
            $list_sales_quotation = $list_sales_quotation->orderBy($order_by, $order_type);
        } else {
            $list_sales_quotation = $list_sales_quotation->orderByStandard();
        }

        if ($date_from) {
            $list_sales_quotation = $list_sales_quotation->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_sales_quotation = $list_sales_quotation->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_sales_quotation = $list_sales_quotation->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_sales_quotation;
    }

    public static function create(Request $request, $formulir)
    {
        $sales_quotation = new SalesQuotation;

        $sales_quotation->required_date = date_format_db($request->input('form_date'));
        $sales_quotation->formulir_id = $formulir->id;
        $sales_quotation->person_id = $request->input('person_id');
        $sales_quotation->type_of_tax = $request->input('type_of_tax');
        $sales_quotation->expedition_fee = number_format_db($request->input('expedition_fee'));
        $sales_quotation->save();

        $subtotal = 0;

        for ($i=0 ; $i<count($request->input('item_id')) ; $i++) {
            $sales_quotation_detail = new SalesQuotationItem;
            $sales_quotation_detail->point_sales_quotation_id = $sales_quotation->id;
            $sales_quotation_detail->item_id = $request->input('item_id')[$i];
            $sales_quotation_detail->quantity = number_format_db($request->input('item_quantity')[$i]);
            $sales_quotation_detail->price = number_format_db($request->input('item_price')[$i]);
            $sales_quotation_detail->discount = number_format_db($request->input('item_discount')[$i]);
            $sales_quotation_detail->unit = $request->input('item_unit')[$i];
            $sales_quotation_detail->allocation_id = $request->input('allocation_id')[$i];
            $sales_quotation_detail->converter = 1;
            $sales_quotation_detail->save();

            $subtotal += ($sales_quotation_detail->quantity * $sales_quotation_detail->price) - ($sales_quotation_detail->quantity * $sales_quotation_detail->price/100 * $sales_quotation_detail->discount);
        }

        $discount = number_format_db($request->input('discount'));
        $tax_base = $subtotal -($subtotal/100 * $discount);
        $tax = 0;

        if ($request->input('type_of_tax') == 'exclude') {
            $tax = $tax_base * 10 / 100;
        }
        if ($request->input('type_of_tax') == 'include') {
            $tax_base =  $tax_base * 100 / 111;
            $tax =  $tax_base * 11 / 100;
        }

        $sales_quotation->subtotal = $subtotal;
        $sales_quotation->discount = $discount;
        $sales_quotation->tax_base = $tax_base;
        $sales_quotation->tax = $tax;
        $sales_quotation->total = $tax_base + $tax + $sales_quotation->expedition_fee;
        $sales_quotation->save();

        return $sales_quotation;
    }

    public static function availableToOrder()
    {
        return SalesQuotation::joinFormulir()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->selectOriginal()
            ->paginate(100);
    }
}
