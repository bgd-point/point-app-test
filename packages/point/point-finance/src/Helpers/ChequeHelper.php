<?php

namespace Point\PointFinance\Helpers;

use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Formulir;
use Point\PointFinance\Models\Cheque\Cheque;
use Point\PointFinance\Models\Cheque\ChequeDetail;
use Point\PointFinance\Models\PaymentReference;

class ChequeHelper
{
	public static function checkIsRejected($formulir_id)
	{
		$cheque = ChequeDetail::where('rejected_formulir_id', $formulir_id)->first();
		if ($cheque) {
        	self::close($formulir_id);
        	return true;
		}

	    FormulirHelper::close($formulir_id);
		return false;
	}

	public static function close($formulir_id)
	{
		$payment_reference_total = 0;
		$payment_reference = PaymentReference::where('payment_reference_id', $formulir_id)->whereNotNull('point_finance_payment_id')->selectRaw('sum(total) as total')->first();
		if ($payment_reference) {
			$payment_reference_total = $payment_reference->total;
		}

		$cheque = Cheque::where('formulir_id', $formulir_id)->first();
		if (! $cheque) {
			$cheque = ChequeDetail::where('rejected_formulir_id', $formulir_id)->first()->cheque;
			$payment_reference = PaymentReference::where('payment_reference_id', $cheque->formulir_id)->whereNotNull('point_finance_payment_id')->selectRaw('sum(total) as total')->first();
			if ($payment_reference) {
				$payment_reference_total += $payment_reference->total;
			}
		}
		
		$total_cheque_detail = ChequeDetail::where('point_finance_cheque_id', $cheque->id)
			->where('status', 1)
            ->selectRaw('sum(amount) as amount')
            ->first();

        $total = $total_cheque_detail->amount + $payment_reference_total;

        if ($cheque->total == $total) {
        	FormulirHelper::close($cheque->formulir_id);
        }

        return true;
	}

	public static function open($formulir_id)
	{
		$formulir = Formulir::find($formulir_id);
		$formulir->form_status = 0;
		$formulir->save();

		return true;
	}
}