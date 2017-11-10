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
    public static function searchList($payment_orders, $date_from, $date_to, $search)
    {
        if ($date_from) {
            $payment_orders = $payment_orders->where('form_date', '>=', \DateHelper::formatDB($date_from, 'start'));
        }

        if ($date_to) {
            $payment_orders = $payment_orders->where('form_date', '<=', \DateHelper::formatDB($date_to, 'end'));
        }

        if ($search) {
            $payment_orders = $payment_orders->where(function ($q) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('form_number', 'like', '%'.$search.'%');
            });
        }

        return $payment_orders;
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
