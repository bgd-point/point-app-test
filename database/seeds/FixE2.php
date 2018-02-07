<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;

class FixE2 extends Seeder
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
                    $journals = Journal::where('form_journal_id', $invoice->formulir_id)->whereIn('coa_id', $coas)->get();
                    foreach ($journals as $journal) {
                        $model = $journal->formulir->formulirable_type;
                        $bpinvoice = $model::find($journal->formulir->formulirable_id);

                        if ('Point\PointPurchasing\Models\Inventory\Invoice' == $journal->formulir->formulirable_type) {
                            foreach ($bpinvoice->items as $bpitem) {
                                if ($bpitem->item_id == $journal->subledger_id) {
                                    $price = $bpitem->quantity * $bpitem->price + ($bpitem->quantity * $bpitem->price * $bpitem->discount / 100);
                                }
                            }
                        }

                        $inventory = Inventory::where('formulir_id', $invoice->formulir_id)->first();
                        $expedition_fee = ($price / $invoice->subtotal * $good_received->expedition_fee) / $inventory->quantity;
                        \Log::info($expedition_fee .'='. $price .'/'. $invoice->subtotal .'*'. $good_received->expedition_fee . ' | '. $inventory->quantity);
                        \Log::info($inventory->price .'+'. $expedition_fee .' | '. $good_received->expedition_fee);

                        $inventory->price += $expedition_fee;
                        $inventory->save();
                    }
                }
            }
        }
        \Log::info('end: ' . $start);
        \DB::commit();
    }
}
