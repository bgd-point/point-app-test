<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\PointPurchasing\Models\Service\Downpayment;

class ServiceDownpaymentHelper
{
    public static function searchList($list_downpayment, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($order_by) {
            $list_downpayment = $list_downpayment->orderBy($order_by, $order_type);
        } else {
            $list_downpayment = $list_downpayment->orderByStandard();
        }

        if ($status != 'all') {
            $list_downpayment = $list_downpayment->where('formulir.form_status', '=', $status ?: 0);
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
        $downpayment->supplier_id = $request->input('supplier_id');
        $downpayment->amount = number_format_db($request->input('amount'));
        $downpayment->remaining_amount = 0;
        $downpayment->payment_type = $request->input('payment_type');
        $downpayment->save();

        return $downpayment;
    }
}
