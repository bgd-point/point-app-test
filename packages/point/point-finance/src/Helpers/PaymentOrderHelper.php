<?php

namespace Point\PointFinance\Helpers;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Core\Models\Vesa;
use Point\Framework\Helpers\FormulirHelper;
use Point\PointFinance\Models\PaymentOrder\PaymentOrder;
use Point\PointFinance\Models\PaymentOrder\PaymentOrderDetail;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;

class PaymentOrderHelper
{
    public static function searchList($list_payment_order, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($order_by) {
            $list_payment_order = $list_payment_order->orderBy($order_by, $order_type);
        } else {
            $list_payment_order = $list_payment_order->orderByStandard();
        }

        if ($status != 'all') {
            $list_payment_order = $list_payment_order->where('formulir.form_status', '=', $status ?: 0);
        }

        if ($date_from) {
            $list_payment_order = $list_payment_order->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_payment_order = $list_payment_order->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_payment_order = $list_payment_order->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_payment_order;
    }

    public static function create(Request $request, $formulir)
    {
        $payment_order = new PaymentOrder;
        $payment_order->formulir_id = $formulir->id;
        $payment_order->person_id = app('request')->input('person_id');
        $payment_order->payment_type = app('request')->input('payment_type');
        $payment_order->total = app('request')->input('total');
        if (app('request')->input('cash_advance_id')) {
            $payment_order->cash_advance_id = app('request')->input('cash_advance_id');
        }

        if (! $payment_order->save()) {
            gritter_error('create has been failed', false);
        }

        $total = 0;
        for ($i=0 ; $i< count(app('request')->input('coa_id')) ; $i++) {
            $payment_order_detail = new PaymentOrderDetail;
            $payment_order_detail->point_finance_payment_order_id = $payment_order->id;
            $payment_order_detail->coa_id = app('request')->input('coa_id')[$i];
            $payment_order_detail->allocation_id = app('request')->input('coa_allocation_id')[$i];
            $payment_order_detail->notes_detail = app('request')->input('other_notes')[$i];
            $payment_order_detail->amount = app('request')->input('coa_value')[$i];
            $payment_order_detail->save();
            $total += $payment_order_detail->amount;
        }

        if ($total == 0) {
            throw new PointException('Zero value not accepted, Please add at least on transaction');
        }

        $payment_order->total = $total;
        $payment_order->save();

        return $payment_order;
    }
}
