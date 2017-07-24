<?php

namespace Point\Framework\Helpers;

use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\AccountPayableAndReceivableDetail;

class AccountPayableAndReceivableHelper
{

    /**
     * Get deficiency
     *
     * @param $account_payable_and_receivable_id
     *
     * @return mixed
     */
    public static function getDeficiency($account_payable_and_receivable_id)
    {
        $account_payable_and_receivable = AccountPayableAndReceivable::find($account_payable_and_receivable_id);
        $total_amount = $account_payable_and_receivable->amount;

        $amount_of_pay = AccountPayableAndReceivableDetail::where('account_payable_and_receivable_id', $account_payable_and_receivable_id)->sum('amount');

        return $total_amount - $amount_of_pay;
    }

    /**
     * Check this payable or receivable is done or not
     *
     * @param $account_payable_and_receivable_id
     *
     * @return boolean
     */
    public static function isDone($account_payable_and_receivable_id)
    {
        if (self::getDeficiency($account_payable_and_receivable_id) != 0) {
            return false;
        }

        return true;
    }

    /**
     * Update status done
     *
     * @param $account_payable_and_receivable_id
     */
    public static function updateStatus($account_payable_and_receivable_id)
    {
        $account_payable_and_receivable = AccountPayableAndReceivable::find($account_payable_and_receivable_id);
        $account_payable_and_receivable->done = self::isDone($account_payable_and_receivable_id);
        $account_payable_and_receivable->save();
    }
    
    /**
     * Remove account payable when edit process
     *
     * @param  $formulir_reference_id
     */
    public static function remove($formulir_reference_id)
    {
        $list_account_payable_and_receivable_detail = AccountPayableAndReceivableDetail::where('formulir_reference_id', $formulir_reference_id)->get();
        foreach ($list_account_payable_and_receivable_detail as $account_payable_and_receivable_detail) {
            $account_payable_and_receivable_detail->parent->done = 0;
            $account_payable_and_receivable_detail->parent->save();
            $account_payable_and_receivable_detail->delete();
        }
        AccountPayableAndReceivable::where('formulir_reference_id', $formulir_reference_id)->delete();
    }
}
