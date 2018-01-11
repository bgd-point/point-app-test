<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Journal;

class FixExpeditionSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $invoices = \Point\PointPurchasing\Models\Inventory\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
        foreach ($invoices as $invoice) {
            $lock = FormulirLock::where('locking_id', $invoice->formulir_id)->where('locked', true)->first();
            if ($lock->lockedForm->formulirable_type == 'Point\PointPurchasing\Models\Inventory\GoodsReceived') {
                $good_received = \Point\PointPurchasing\Models\Inventory\GoodsReceived::where('formulir_id', $lock->locked_id)->first();
                $journals = Journal::where('form_journal_id', $invoice->formulir_id)->where('coa_id', 14)->get();
                foreach ($journals as $journal) {
                    $journal->debit += $journal->debit / $invoice->subtotal * $good_received->expedition_fee;
                    $journal->save();
                }
            }
        }

        \DB::commit();
    }
}
