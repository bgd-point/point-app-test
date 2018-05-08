<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Journal;
use Point\PointFinance\Models\Bank\Bank;
use Point\PointFinance\Models\Cash\Cash;

class FixSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        

        $fixes_cash = \Point\Framework\Models\AccountPayableAndReceivable::join('formulir', 'formulir.id', '=', 'formulir_reference_id')
            ->where('formulirable_type', '=', 'Point\PointFinance\Models\Cash\Cash')
            ->get();

        foreach ($fixes_cash as $fix_cash) {
            echo $fix_cash->formulirable_id . PHP_EOL;
            $payment = Cash::find($fix_cash->formulirable_id);

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


            if (str_contains($payment->formulir->form_number, '-IN')) {
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

        $fixes_bank = \Point\Framework\Models\AccountPayableAndReceivable::join('formulir', 'formulir.id', '=', 'formulir_reference_id')
            ->where('formulirable_type', '=', 'Point\PointFinance\Models\Bank\Bank')
            ->get();

        foreach ($fixes_bank as $fix_bank) {
            echo $fix_bank->formulirable_id . PHP_EOL;
            $payment = Bank::find($fix_bank->formulirable_id);

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


            if (str_contains($payment->formulir->form_number, '-IN')) {
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

        \DB::commit();
    }
}
