<?php

namespace Point\BumiDeposit\Helpers;

use Illuminate\Http\Request;

use Point\BumiDeposit\Models\Deposit;

class DepositHelper
{
    public static function searchList($list_deposit, $date_from, $date_to, $search, $select_field)
    {
        if ($date_from) {
            $list_deposit = $list_deposit->where(function ($q) use ($date_from) {
                $q->where('due_date', '>=', date_format_db($date_from, 'start'));
            });
        }

        if ($date_to) {
            $list_deposit = $list_deposit->where(function ($q) use ($date_to) {
                $q->where('due_date', '<=', date_format_db($date_to, 'end'));
            });
        }

        if ($select_field == 'form_number') {
            $list_deposit = $list_deposit->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%');
            });
        } elseif ($select_field == 'group') {
            $list_deposit = $list_deposit->where(function ($q) use ($search) {
                $q->where('bumi_deposit_group.name', 'like', '%'.$search.'%');
            });
        } elseif ($select_field == 'bank') {
            $list_deposit = $list_deposit->where(function ($q) use ($search) {
                $q->where('bumi_deposit_bank.name', 'like', '%'.$search.'%');
            });
        } elseif ($select_field == 'bilyet') {
            $list_deposit = $list_deposit->where(function ($q) use ($search) {
                $q->where('bumi_deposit.deposit_number', 'like', '%'.$search.'%');
            });
        } elseif ($select_field == 'category') {
            $list_deposit = $list_deposit->where(function ($q) use ($search) {
                $q->where('bumi_deposit_category.name', 'like', '%'.$search.'%');
            });
        } elseif ($select_field == 'notes') {
            $list_deposit = $list_deposit->where(function ($q) use ($search) {
                $q->where('formulir.notes', 'like', '%'.$search.'%');
            });
        } elseif ($select_field == 'deposit') {
            $list_deposit = $list_deposit->where(function ($q) use ($search) {
                $q->where('bumi_deposit.total_amount', 'like', '%'.$search.'%');
            });
        } elseif ($select_field == 'withdrawal') {
            $list_deposit = $list_deposit->where(function ($q) use ($search) {
                $q->where('bumi_deposit.withdraw_amount', 'like', '%'.$search.'%');
            });
        } else {
            $list_deposit = $list_deposit->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                    ->orWhere('bumi_deposit_bank.name', 'like', '%'.$search.'%')
                    ->orWhere('bumi_deposit_category.name', 'like', '%'.$search.'%')
                    ->orWhere('bumi_deposit_group.name', 'like', '%'.$search.'%')
                    ->orWhere('bumi_deposit.total_amount', 'like', '%'.$search.'%')
                    ->orWhere('formulir.notes', 'like', '%'.$search.'%');
            });
        }

        return $list_deposit;
    }

    public static function withdraw(Request $request, $deposit)
    {
        $deposit->withdraw_date = date_format_db($request->input('withdraw_date'));
        $deposit->withdraw_amount = number_format_db($request->input('withdraw_amount'));
        $deposit->withdraw_notes = $request->input('withdraw_notes');
        $deposit->withdraw_approval_status = 1;
        $deposit->withdraw_approval_to = 1;
        $deposit->save();

        $deposit->formulir->form_status = 1;
        $deposit->formulir->save();
        return $deposit;
    }

    public static function extend(Request $request, $formulir, $deposit_old)
    {
        // create new deposit
        $deposit = new Deposit;
        $deposit->formulir_id = $formulir->id;
        $deposit->deposit_bank_id = $deposit_old->deposit_bank_id;
        $deposit->deposit_bank_account_id = $deposit_old->deposit_bank_account_id;
        $deposit->deposit_bank_product_id = $deposit_old->deposit_bank_product_id;
        $deposit->deposit_category_id = $deposit_old->deposit_category_id;
        $deposit->deposit_group_id = $deposit_old->deposit_group_id;
        $deposit->deposit_owner_id = $deposit_old->deposit_owner_id;
        ;
        $deposit->deposit_number = number_format_db($request->input('deposit_number'));
        $deposit->deposit_time = number_format_db($request->input('deposit_time'));
        $deposit->due_date = date_format_db($request->input('due_date'));
        $deposit->original_amount = number_format_db($request->input('original_amount'));
        $deposit->interest_percent = number_format_db($request->input('interest_percent'));
        $deposit->interest_value = number_format_db($request->input('interest_percent') / 100 * $deposit->original_amount);
        $deposit->tax_percent = number_format_db($request->input('tax_percent'));
        $deposit->tax_value = number_format_db($request->input('tax_percent') / 100 * $deposit->interest_value);
        $deposit->total_days_in_year = number_format_db($request->input('total_days_in_year'));
        $deposit->total_interest = number_format_db($request->input('total_interest'));
        $deposit->total_amount = number_format_db($request->input('total_amount'));
        $deposit->important_notes = number_format_db($request->input('important_notes'));
        $deposit->reference_deposit_id = $deposit_old->id;
        $deposit->save();

        // close form deposit
        $deposit_old->withdraw_date = date_format_db($request->input('form_date'));
        $deposit_old->withdraw_amount = number_format_db($request->input('original_amount'));
        $deposit_old->withdraw_approval_status = 1;
        $deposit_old->withdraw_approval_to = 1;
        $deposit_old->save();

        $deposit_old->formulir->form_status = 1;
        $deposit_old->formulir->save();

        $deposit->reference_deposit_id = $deposit_old->id;
        $deposit->save();

        return $deposit;
    }

    public static function create(Request $request, $formulir)
    {
        $deposit = new Deposit;
        $deposit->formulir_id = $formulir->id;
        $deposit->deposit_bank_id = $request->input('deposit_bank_id');
        $deposit->deposit_bank_account_id = $request->input('deposit_bank_account_id');
        $deposit->deposit_bank_product_id = $request->input('deposit_bank_product_id');
        $deposit->deposit_category_id = $request->input('deposit_category_id');
        $deposit->deposit_group_id = $request->input('deposit_group_id');
        $deposit->deposit_owner_id = $request->input('deposit_owner_id');
        $deposit->deposit_number = $request->input('deposit_number');
        $deposit->deposit_time = number_format_db($request->input('deposit_time'));
        $deposit->due_date = date_format_db($request->input('due_date'));
        $deposit->original_amount = number_format_db($request->input('original_amount'));
        $deposit->interest_percent = number_format_db($request->input('interest_percent'));
        $deposit->interest_value = number_format_db($request->input('interest_percent') / 100 * $deposit->original_amount);
        $deposit->tax_percent = number_format_db($request->input('tax_percent'));
        $deposit->tax_value = number_format_db($request->input('tax_percent') / 100 * $deposit->interest_value);
        $deposit->total_days_in_year = number_format_db($request->input('total_days_in_year'));
        $deposit->total_interest = number_format_db($request->input('total_interest'));
        $deposit->total_amount = number_format_db($request->input('total_amount'));
        $deposit->important_notes = number_format_db($request->input('important_notes'));
        $deposit->save();

        return $deposit;
    }
}
