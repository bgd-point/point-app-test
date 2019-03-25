<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\AccountPayableAndReceivableDetail;
use Point\Framework\Models\Journal;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\Retur;

class DebtChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debt:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check unbalance journal';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $journals = Journal::where('coa_id', 4)->get();
        info('Check : ' . $journals->count());
        foreach ($journals as $journal) {
            if ($journal->debit > 0) {
                $debts = AccountPayableAndReceivable::where('formulir_reference_id', $journal->form_journal_id)->get();
                $check = false;
                foreach ($debts as $debt) {
                    if ($journal->debit == $debt->amount) {
                        $check = true;
                        break;
                    }
                }

                if ($check == false) {
                    info('DEBIT ' . $journal->formulir->id . '. ' . $journal->formulir->form_number . ' ' . $journal->debit);
                }
            } else {
                $pays = AccountPayableAndReceivableDetail::where('formulir_reference_id', $journal->form_journal_id)->get();
                $check = false;
                foreach ($pays as $pay) {
                    if ($journal->credit == $pay->amount) {
                        $check = true;
                        break;
                    }
                }

                if ($check == false) {
                    info('CREDIT ' . $journal->formulir->id . '. ' . $journal->formulir->form_number . ' ' . $journal->credit);
                }
            }
        }

        $aprs = AccountPayableAndReceivable::where('reference_type', Retur::class)->get();

        foreach ($aprs as $apr) {
            $apr->delete();
        }

        $aprs = AccountPayableAndReceivableDetail::join('formulir', 'formulir.id', '=', 'account_payable_and_receivable_detail.formulir_reference_id')
            ->where('formulir.formulirable_type', Retur::class)
            ->select('account_payable_and_receivable_detail.*')
            ->get();

        $this->line('COUNT : ' . $aprs->count());

        foreach ($aprs as $apr) {
            $apr->delete();
        }

//        $returs = Retur::all();
//        foreach ($returs as $retur) {
//            $invoice = Invoice::find($retur->point_sales_invoice_id);
//            $apr = AccountPayableAndReceivable::where('formulir_reference_id', $invoice->formulir_id)->first();
//
//            $aprd = new AccountPayableAndReceivableDetail;
//            $aprd->form_date = $retur->formulir->form_date;
//            $aprd->account_payable_and_receivable_id = $apr->id;
//            $aprd->formulir_reference_id = $retur->formulir->id;
//            $aprd->amount = $retur->total;
//            $aprd->notes = 'RETUR';
//            $aprd->save();
//        }
    }
}
