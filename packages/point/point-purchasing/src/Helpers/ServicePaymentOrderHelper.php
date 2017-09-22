<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\PointPurchasing\Models\Service\PaymentOrder;
use Point\PointPurchasing\Models\Service\PaymentOrderDetail;
use Point\PointPurchasing\Models\Service\PaymentOrderOther;

class ServicePaymentOrderHelper
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
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                    ->orWhere('person.name', 'like', '%'.$search.'%');
            });
        }

        return $list_payment_order;
    }

    public static function create(Request $request, $formulir, $references, $references_account, $references_type, $references_id, $references_amount, $references_amount_original, $references_notes, $references_amount_edit = [])
    {
        $payment_order = new PaymentOrder;
        $payment_order->formulir_id = $formulir->id;
        $payment_order->person_id = $request->input('person_id');
        $payment_order->payment_type = $request->input('payment_type');
        $payment_order->save();

        $total = 0;
        for ($i=0 ; $i < count($references) ; $i++) {
            $reference = $references[$i];
            
            if ($references_amount[$i] > $references_amount_original[$i]) {
                throw new PointException("AMOUNT FROM ".$reference->formulir->form_number." CAN NOT BE MORE THAN ". number_format_price($references_amount_original[$i]));
            }

            $payment_order_detail = new PaymentOrderDetail;
            $payment_order_detail->point_purchasing_service_payment_order_id = $payment_order->id;
            $payment_order_detail->detail_notes = $references_notes[$i];
            $payment_order_detail->amount = $references_amount[$i];
            $payment_order_detail->form_reference_id = $reference->formulir_id;
            $payment_order_detail->coa_id = $references_account[$i];
            $payment_order_detail->save();
            
            $total += $payment_order_detail->amount;

            ReferHelper::create(
                $references_type[$i],
                $references_id[$i],
                get_class($payment_order_detail),
                $payment_order_detail->id,
                get_class($payment_order),
                $payment_order->id,
                $payment_order_detail->amount
            );

            $close_status = ReferHelper::closeStatus(
                $references_type[$i],
                $references_id[$i],
                $references_amount_original[$i],
                $references_amount_edit ? $references_amount_edit[$i] : 0
            );

            formulir_lock($reference->formulir_id, $payment_order->formulir_id);

            if ($close_status) {
                $reference->formulir->form_status = 1;
                $reference->formulir->save();
            }
        }

        for ($i=0 ; $i < count($request->input('coa_id')) ; $i++) {
            $payment_order_other = new PaymentOrderOther;
            $payment_order_other->point_purchasing_service_payment_order_id = $payment_order->id;
            $payment_order_other->coa_id = $request->input('coa_id')[$i];
            $payment_order_other->other_notes = $request->input('coa_notes')[$i];
            $payment_order_other->amount = number_format_db($request->input('coa_amount')[$i]);
            $payment_order_other->allocation_id = $request->input('allocation_id')[$i];
            $payment_order_other->save();

            $total += $payment_order_other->amount;
        }

        $payment_order->total_payment = $total;
        $payment_order->save();
        
        return $payment_order;
    }
}
