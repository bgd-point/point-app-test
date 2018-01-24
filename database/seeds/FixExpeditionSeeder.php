<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;

class FixExpeditionSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $invoices = \Point\PointPurchasing\Models\Inventory\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
        $start = 0;
        \Log::info('start: ' . $invoices->count());
        foreach ($invoices as $invoice) {
            $lock = FormulirLock::where('locking_id', $invoice->formulir_id)->where('locked', true)->first();
            if ($lock->lockedForm->formulirable_type == 'Point\PointPurchasing\Models\Inventory\GoodsReceived') {
                $good_received = \Point\PointPurchasing\Models\Inventory\GoodsReceived::where('formulir_id', $lock->locked_id)->first();
                $coas = \Point\Framework\Models\Master\Item::select('account_asset_id')->groupBy('account_asset_id')->get();
                if ($coas) {
                    $start++;
//                    $journals = Journal::where('form_journal_id', $invoice->formulir_id)->whereIn('coa_id', $coas)->get();
//
//                    foreach ($journals as $journal) {
//                        $journal->debit += $journal->debit / $invoice->subtotal * $good_received->expedition_fee;
//                        $journal->save();
//                    }

                    $inventories = Inventory::where('formulir_id', $invoice->formulir_id)->get();
                    \Log::info($inventories->count());
                    foreach ($inventories as $inventory) {
                        $expedition_fee =  $inventory->total_value / $invoice->subtotal * $good_received->expedition_fee;
                        $inventory->price += $expedition_fee;
                        $inventory->save();
                    }
                }
            }

//            $account_payable_expedition = JournalHelper::getAccount('point expedition', 'account payable - expedition');
//            $position = JournalHelper::position($account_payable_expedition);
//            $journal = new Journal;
//            $journal->form_date = $invoice->formulir->form_date;
//            $journal->coa_id = $account_payable_expedition;
//            $journal->description = 'expedition "' . $invoice->formulir->form_number . '"';
//            $journal->$position = $good_received->expedition_fee;
//            $journal->form_journal_id = $invoice->formulir_id;
//            $journal->form_reference_id;
//            $journal->subledger_id = $invoice->id;
//            $journal->subledger_type = get_class($invoice);
//            $journal->save();
        }
        \Log::info('end: ' . $start);
        \DB::commit();
    }
}
