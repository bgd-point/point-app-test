<?php

namespace Point\PointPurchasing\Helpers\FixedAssets;

use Illuminate\Http\Request;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsDownpayment;

class FixedAssetsDownpaymentHelper
{
    public static function searchList($list_downpayment, $date_from, $date_to, $search)
    {
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
        $downpayment = new FixedAssetsDownpayment;
        $downpayment->formulir_id = $formulir->id;
        $downpayment->supplier_id = $request->input('supplier_id');
        $downpayment->amount = number_format_db($request->input('amount'));
        $downpayment->fixed_assets_order_id = $request->input('order_reference') ? : "";
        $downpayment->payment_type = $request->input('payment_type');
        $downpayment->remaining_amount = number_format_db($request->input('amount'));
        $downpayment->save();

        return $downpayment;
    }
}
