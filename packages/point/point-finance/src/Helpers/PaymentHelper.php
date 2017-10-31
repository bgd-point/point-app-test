<?php

namespace Point\PointFinance\Helpers;

use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Journal;
use Point\PointFinance\Models\Bank\Bank;
use Point\PointFinance\Models\Bank\BankDetail;
use Point\PointFinance\Models\Cash\Cash;
use Point\PointFinance\Models\Cash\CashCashAdvance;
use Point\PointFinance\Models\Cash\CashDetail;
use Point\PointFinance\Models\PaymentReference;

class PaymentHelper
{
    public static function searchAvailablePayableReference($payment_type)
    {
        return PaymentReference::joinFormulir()
            ->where('payment_flow', '=', 'out')
            ->where('point_finance_payment_id', '=', null)
            ->where('payment_type', '=', $payment_type)
            ->selectOriginal()
            ->orderByStandard()
            ->paginate(100);
    }

    public static function searchAvailableReceivableReference($payment_type)
    {
        return PaymentReference::joinFormulir()
            ->where('payment_flow', '=', 'in')
            ->where('point_finance_payment_id', '=', null)
            ->where('payment_type', '=', $payment_type)
            ->selectOriginal()
            ->orderByStandard()
            ->paginate(100);
    }

    public static function cashOut($formulir)
    {

        $payment_reference = PaymentReference::find(app('request')->input('payment_reference_id'));

        $cash = new Cash;
        $cash->formulir_id = $formulir->id;
        $cash->person_id = app('request')->input('person_id');
        $cash->coa_id = app('request')->input('account_cash_id');
        $cash->payment_flow = $payment_reference->payment_flow;
        $cash->total = number_format_db(app('request')->input('total')) * -1;
        $cash->save();

        self::updatePaymentReference($payment_reference, $formulir->id);

        if ($cash->cash_advance_id) {
            $cash->cashAdvance->remaining_amount = 0;
            $cash->cashAdvance->save();
        }

        for ($i = 0; $i < count(app('request')->input('cash_advance_id')); $i++) {
            $cash_cash_advance = new CashCashAdvance;
            $cash_cash_advance->point_finance_cash_id = $cash->id;
            $cash_cash_advance->cash_advance_id = app('request')->input('cash_advance_id')[0];
            $cash_cash_advance->cash_advance_amount = app('request')->input('cash_advance_amount')[0];
            $cash_cash_advance->close = app('request')->input('close')[0] == 'on' ? 1 : 0;
            $cash_cash_advance->save();
        }

        if ($cash_cash_advance->close) {
            $cash_cash_advance->cashAdvance->remaining_amount = 0;
            $cash_cash_advance->cashAdvance->save();
        }

        $total_amount = 0;
        for ($i=0 ; $i<count(app('request')->input('notes_detail')) ; $i++) {
            $cash_detail = new CashDetail;
            $cash_detail->point_finance_cash_id = $cash->id;
            $cash_detail->coa_id = app('request')->input('coa_id')[$i];
            $cash_detail->notes_detail = app('request')->input('notes_detail')[$i];
            $cash_detail->amount = number_format_db(app('request')->input('amount')[$i]);
            $cash_detail->allocation_id = number_format_db(app('request')->input('allocation_id')[$i]);
            $cash_detail->form_reference_id = app('request')->input('formulir_reference_id')[$i] ?: null;
            $cash_detail->subledger_id = app('request')->input('person_id')  ?: null;
            $cash_detail->subledger_type = app('request')->input('formulir_reference_class')[$i]  ?: null;
            $cash_detail->reference_id = app('request')->input('reference_id')[$i] ?: null;
            $cash_detail->reference_type = app('request')->input('reference_type')[$i]?: null;
            $cash_detail->save();

            $total_amount += $cash_detail->amount;
        }

        $cash->total = $total_amount;

        FormulirHelper::close($payment_reference->payment_reference_id);
        FormulirHelper::close($cash->formulir->id);
        FormulirHelper::lock($payment_reference->payment_reference_id, $formulir->id);
        self::journal($cash);

        return $cash;
    }

    public static function cashIn($formulir)
    {
        $payment_reference = PaymentReference::find(app('request')->input('payment_reference_id'));

        $cash = new Cash;
        $cash->formulir_id = $formulir->id;
        $cash->person_id = app('request')->input('person_id');
        $cash->coa_id = app('request')->input('account_cash_id');
        $cash->payment_flow = 'in';
        $cash->total = number_format_db(app('request')->input('total'));
        $cash->save();

        $count = 0;
        for ($i=0 ; $i<count(app('request')->input('coa_id')) ; $i++) {
            if (app('request')->input('coa_id')[$i] == ''
                || app('request')->input('amount')[$i] == 0) {
                continue;
            }
            $cash_detail = new CashDetail;
            $cash_detail->point_finance_cash_id = $cash->id;
            $cash_detail->coa_id = app('request')->input('coa_id')[$i];
            $cash_detail->notes_detail = app('request')->input('notes_detail')[$i];
            $cash_detail->amount = number_format_db(app('request')->input('amount')[$i]);
            $cash_detail->allocation_id = number_format_db(app('request')->input('allocation_id')[$i]);
            $cash_detail->form_reference_id = app('request')->input('formulir_reference_id')[$i] ?: null;
            $cash_detail->subledger_id = app('request')->input('person_id')  ?: null;
            $cash_detail->subledger_type = app('request')->input('formulir_reference_class')[$i]  ?: null;
            $cash_detail->reference_id = app('request')->input('reference_id')[$i] ?: null;
            $cash_detail->reference_type = app('request')->input('reference_type')[$i]?: null;
            $cash_detail->save();
            $count++;
        }

        if ($count == 0) {
            throw new PointException('Cannot save empty payment, please check your input');
        }

        if ($payment_reference) {
            self::updatePaymentReference($payment_reference, $formulir->id);
            FormulirHelper::close($payment_reference->payment_reference_id);
            FormulirHelper::lock($payment_reference->payment_reference_id, $formulir->id);
        }

        FormulirHelper::close($cash->formulir->id);

        self::journal($cash);

        return $cash;
    }

    public static function bankOut($formulir)
    {
        $payment_reference = PaymentReference::find(app('request')->input('payment_reference_id'));

        $bank = new Bank;
        $bank->formulir_id = $formulir->id;
        $bank->person_id = app('request')->input('person_id');
        $bank->coa_id = app('request')->input('account_bank_id');
        $bank->payment_flow = $payment_reference->payment_flow;
        $bank->total = number_format_db(app('request')->input('total')) * -1;
        $bank->save();

        self::updatePaymentReference($payment_reference, $formulir->id);

        for ($i=0 ; $i<count(app('request')->input('notes_detail')) ; $i++) {
            $bank_detail = new BankDetail;
            $bank_detail->point_finance_bank_id = $bank->id;
            $bank_detail->coa_id = app('request')->input('coa_id')[$i];
            $bank_detail->notes_detail = app('request')->input('notes_detail')[$i];
            $bank_detail->amount = number_format_db(app('request')->input('amount')[$i]);
            $bank_detail->allocation_id = number_format_db(app('request')->input('allocation_id')[$i]);
            $bank_detail->form_reference_id = app('request')->input('formulir_reference_id')[$i] ?: null;
            $bank_detail->subledger_id = app('request')->input('person_id')  ?: null;
            $bank_detail->subledger_type = app('request')->input('formulir_reference_class')[$i]  ?: null;
            $bank_detail->reference_id = app('request')->input('reference_id')[$i] ?: null;
            $bank_detail->reference_type = app('request')->input('reference_type')[$i]?: null;
            $bank_detail->save();
        }

        FormulirHelper::close($payment_reference->payment_reference_id);
        FormulirHelper::close($bank->formulir->id);
        FormulirHelper::lock($payment_reference->payment_reference_id, $formulir->id);
        self::journal($bank);

        return $bank;
    }

    public static function bankIn($formulir)
    {
        $bank = new Bank;
        $bank->formulir_id = $formulir->id;
        $bank->person_id = app('request')->input('person_id');
        $bank->coa_id = app('request')->input('account_bank_id');
        $bank->payment_flow = 'in';
        $bank->total = number_format_db(app('request')->input('total'));
        $bank->save();

        $count = 0;
        for ($i=0 ; $i<count(app('request')->input('coa_id')) ; $i++) {
            if (app('request')->input('coa_id')[$i] == ''
                || app('request')->input('amount')[$i] == 0) {
                continue;
            }
            $bank_detail = new BankDetail;
            $bank_detail->point_finance_bank_id = $bank->id;
            $bank_detail->coa_id = app('request')->input('coa_id')[$i];
            $bank_detail->notes_detail = app('request')->input('notes_detail')[$i];
            $bank_detail->amount = number_format_db(app('request')->input('amount')[$i]);
            $bank_detail->allocation_id = number_format_db(app('request')->input('allocation_id')[$i]);
            $bank_detail->form_reference_id = app('request')->input('formulir_reference_id')[$i] ?: null;
            $bank_detail->subledger_id = app('request')->input('person_id')  ?: null;
            $bank_detail->subledger_type = app('request')->input('formulir_reference_class')[$i]  ?: null;
            $bank_detail->reference_id = app('request')->input('reference_id')[$i] ?: null;
            $bank_detail->reference_type = app('request')->input('reference_type')[$i]?: null;
            $bank_detail->save();
            $count++;
        }

        if ($count == 0) {
            throw new PointException('Cannot save empty payment, please check your input');
        }

        $payment_reference = PaymentReference::find(app('request')->input('payment_reference_id'));
        if ($payment_reference) {
            self::updatePaymentReference($payment_reference, $formulir->id);
            FormulirHelper::close($payment_reference->payment_reference_id);
            FormulirHelper::lock($payment_reference->payment_reference_id, $formulir->id);
        }

        FormulirHelper::close($bank->formulir->id);

        self::journal($bank);

        return $bank;
    }

    public static function cancelPaymentReference($payment_reference_id)
    {
        $payment_reference = PaymentReference::where('payment_reference_id', '=', $payment_reference_id)->first();
        if ($payment_reference) {
            $payment_reference->delete();
        }
    }

    public static function cancelPayment($payment_id)
    {
        $payment_reference = PaymentReference::where('point_finance_payment_id', '=', $payment_id)->first();
        if ($payment_reference) {
            $payment_reference->point_finance_payment_id = null;
            $payment_reference->save();
        }
    }

    public static function updatePaymentReference($payment_reference, $formulir_id)
    {
        $payment_reference->point_finance_payment_id = $formulir_id;
        $payment_reference->save();
    }

    public static function journal($payment)
    {
        // JOURNAL #1 of #2 - PAYMENT TYPE CASH / BANK
        $position = JournalHelper::position($payment->coa_id);
        $journal = new Journal();
        $journal->form_date = $payment->formulir->form_date;
        $journal->coa_id = $payment->coa_id;
        $journal->description = $payment->formulir->notes ?: '';
        $journal->$position = $payment->total;
        $journal->form_journal_id = $payment->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        if ($journal->debit > 0) {
            $position = 'credit';
        } else {
            $position = 'debit';
        }

        // JOURNAL #2 of #2 - PAYMENT DETAIL
        foreach ($payment->detail as $payment_detail) {
            $journal = new Journal();
            $journal->form_date = $payment->formulir->form_date;
            $journal->coa_id = $payment_detail->coa_id;
            $journal->description = $payment_detail->notes_detail;
            $journal->$position = $payment_detail->amount;
            $journal->form_journal_id = $payment->formulir_id;
            $journal->form_reference_id = $payment_detail->form_reference_id;
            $journal->subledger_id = $payment_detail->subledger_id;
            $journal->subledger_type = $payment_detail->subledger_type;
            $journal->save([
                'reference_id' => $payment_detail->reference_id,
                'reference_type' => $payment_detail->reference_type
            ]);
        }
    }
}
