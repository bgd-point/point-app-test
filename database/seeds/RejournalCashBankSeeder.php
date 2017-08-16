<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Journal;
use Point\PointFinance\Models\Bank\Bank;
use Point\PointFinance\Models\Bank\BankDetail;
use Point\PointFinance\Models\Cash\Cash;
use Point\PointFinance\Models\Cash\CashDetail;

class RejournalCashBankSeeder extends Seeder
{
    public function run()
    {
        /**
         * Fix journal cash or bank where it have not reference
         */
        
    	\DB::beginTransaction();
        \Log::info('---- Seeder bank starting ----');
        self::bank();
        \Log::info('---- Seeder bank finished ----');
        \Log::info('---- Seeder cash starting ----');
        self::cash();
        \Log::info('---- Seeder cash finished ----');
        \DB::commit();
    }

    public function bank()
    {
        // $bank_id = BankDetail::whereNull('form_reference_id')->groupBy('point_finance_bank_id')->select('point_finance_bank_id')->get()->toArray();
        $bank_formulir = Bank::joinFormulir()->close()->notArchived()->select('formulir_id')->get()->toArray();
        $journal_emptying = Journal::whereIn('form_journal_id', $bank_formulir)->delete();
        $account_receivable = AccountPayableAndReceivable::whereIn('formulir_reference_id', $bank_formulir)->delete();
        $list_bank = Bank::whereIn('formulir_id', $bank_formulir)->get();
        foreach ($list_bank as $bank) {
            self::journal($bank);
            JournalHelper::checkJournalBalance($bank->formulir_id);
        }
    }

    public function cash()
    {
        $cash_formulir = Cash::joinFormulir()->close()->notArchived()->select('formulir_id')->get()->toArray();
        $journal_emptying = Journal::whereIn('form_journal_id', $cash_formulir)->delete();
        $account_receivable = AccountPayableAndReceivable::whereIn('formulir_reference_id', $cash_formulir)->delete();
        $list_cash = Cash::whereIn('formulir_id', $cash_formulir)->get();
        foreach ($list_cash as $cash) {
            self::journal($cash);
            JournalHelper::checkJournalBalance($cash->formulir_id);
        }
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