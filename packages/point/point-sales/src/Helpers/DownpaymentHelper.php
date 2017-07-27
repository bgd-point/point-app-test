<?php

namespace Point\PointSales\Helpers;

use Illuminate\Http\Request;
use Point\PointSales\Models\Sales\Downpayment;

class DownpaymentHelper
{
    public static function searchList($list_downpayment, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_downpayment = $list_downpayment->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $list_downpayment = $list_downpayment->orderBy($order_by, $order_type);
        } else {
            $list_downpayment = $list_downpayment->orderByStandard();
        }

        if ($date_from) {
            $list_downpayment = $list_downpayment->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_downpayment = $list_downpayment->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_downpayment = $list_downpayment->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_downpayment;
    }

    public static function create(Request $request, $formulir)
    {
        $downpayment = new Downpayment;
        $downpayment->formulir_id = $formulir->id;
        $downpayment->person_id = $request->input('person_id');
        $downpayment->amount = number_format_db($request->input('amount'));
        $downpayment->sales_order_id = $request->input('order_reference');
        $downpayment->cutoff_account_id = $request->input('cutoff_account_id') ? : null;
        $downpayment->remaining_amount = number_format_db($request->input('amount'));
        $downpayment->payment_type = $request->input('payment_type');
        $downpayment->save();

        if ($request->input('order_reference')) {
            // TODO: reject downpayment if value more than sales
        }

        if ($request->input('close')) {
            $formulir->form_status = 1;
            $formulir->save();
        }
        
        return $downpayment;
    }
}
