<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\CutOffPayableDetail;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;
use Point\PointPurchasing\Models\Inventory\PaymentOrder;
use Point\PointPurchasing\Models\Inventory\PaymentOrderDetail;
use Point\PointPurchasing\Models\Inventory\PaymentOrderOther;

class PaymentOrderHelper
{
    public static function searchList($list_payment_order,$order_by, $order_type, $status = 0, $date_from, $date_to, $search)
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

    public static function create(
        Request $request, 
        $formulir, 
        $references, 
        $references_account, 
        $references_type, 
        $references_id, 
        $references_amount, 
        $references_amount_original, 
        $references_notes, 
        $references_detail_id,
        $references_detail_type, 
        $references_amount_edit = []
    ) {
        $payment_order = new PaymentOrder;
        $payment_order->formulir_id = $formulir->id;
        $payment_order->supplier_id = $request->input('supplier_id');
        $payment_order->payment_type = $request->input('payment_type');
        $payment_order->save();

        $total = 0;
        for ($i=0 ; $i < count($references) ; $i++) {
            $reference = $references[$i];
            if (get_class($reference) == get_class(new CutOffPayableDetail())) {
                $reference->formulir_id = $reference->cutoffPayable->formulir_id;
            }

            if ($references_amount[$i] > $references_amount_original[$i]) {
                throw new PointException("AMOUNT FROM ".$reference->formulir->form_number." CAN NOT BE MORE THAN ". number_format_price($references_amount_original[$i]));
            }
            
            $payment_order_detail = new PaymentOrderDetail;
            $payment_order_detail->point_purchasing_payment_order_id = $payment_order->id;
            $payment_order_detail->detail_notes = $references_notes[$i];
            $payment_order_detail->amount = $references_amount[$i];
            $payment_order_detail->coa_id = $references_account[$i];
            $payment_order_detail->form_reference_id = $reference->formulir_id;
            $payment_order_detail->reference_id = $references_detail_id[$i];
            $payment_order_detail->reference_type = $references_detail_type[$i];
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
                0
            );

            formulir_lock($reference->formulir_id, $payment_order->formulir_id);

            if ($close_status) {
                if (get_class($reference) != get_class(new CutOffPayableDetail())) {
                    $reference->formulir->form_status = 1;
                    $reference->formulir->save();
                }
            }
        }

        for ($i=0 ; $i < count($request->input('coa_id')) ; $i++) {
            $payment_order_other = new PaymentOrderOther;
            $payment_order_other->point_purchasing_payment_order_id = $payment_order->id;
            $payment_order_other->coa_id = $request->input('coa_id')[$i];
            $payment_order_other->allocation_id = $request->input('allocation_id')[$i];
            $payment_order_other->other_notes = $request->input('coa_notes')[$i];
            $payment_order_other->amount = number_format_db($request->input('coa_amount')[$i]);
            $payment_order_other->save();

            $total += $payment_order_other->amount;
        }

        $payment_order->total_payment = $total;
        $payment_order->save();

        return $payment_order;
    }
}
