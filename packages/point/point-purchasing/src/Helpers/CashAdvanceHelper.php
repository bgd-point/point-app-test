<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\PointPurchasing\Models\Inventory\CashAdvance;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;

class CashAdvanceHelper
{
    public static function searchList($list_cash_advance, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($order_by) {
            $list_cash_advance = $list_cash_advance->orderBy($order_by, $order_type);
        } else {
            $list_cash_advance = $list_cash_advance->orderByStandard();
        }

        if ($status != 'all') {
            $list_cash_advance = $list_cash_advance->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($date_from) {
            $list_cash_advance = $list_cash_advance->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_cash_advance = $list_cash_advance->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_cash_advance = $list_cash_advance->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        if ((request()->get('database_name') == 'p_test' || request()->get('database_name') == 'p_personalfinance') && auth()->user()->name != 'lioni') {
            $list_cash_advance = $list_cash_advance->join('coa', 'coa.id', '=', 'point_finance_cash_advance.coa_id')
            ->where('coa.name', 'not like', '%lioni%');
        }

        return $list_cash_advance;
    }

    public static function create(Request $request, $formulir)
    {
        $cash_advance = new CashAdvance;
        $cash_advance->formulir_id = $formulir->id;
        $cash_advance->purchase_requisition_id = $request->input('purchase_requisition_id');
        $cash_advance->employee_id = $request->input('employee_id');
        $cash_advance->amount = number_format_db($request->input('amount'));
        $cash_advance->remaining_amount = number_format_db($request->input('amount'));
        $cash_advance->payment_type = $request->input('payment_type');
        $cash_advance->save();

        $purchase_requisition = PurchaseRequisition::find($request->input('purchase_requisition_id'));
        formulir_lock($purchase_requisition->formulir->id, $formulir->id);

        return $cash_advance;
    }
}
