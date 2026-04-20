<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Retur;
use Point\Framework\Models\Journal;

/**
 * Class RecalculateTest
 *
 * This command is designed to recalculate inventory valuation for a specific
 * item and warehouse. It implements a **perpetual inventory** system,
 * likely following a **Moving Average Cost (MAC)** method, by iterating
 * through inventory records chronologically and updating the rolling
 * total quantity, total value, and average cost (cogs).
 */
class RecalculateJournalHpp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:jhpp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'recalculate inventory';

    /**
     * Execute the console command.
     *
     * This method fetches inventory records for item 102 and warehouse 1,
     * sorted by date, and processes them sequentially to recalculate
     * the total quantity, total value, and cost of goods sold (cogs)
     * using the Moving Average Cost (MAC) logic.
     *
     * The logic relies on maintaining rolling totals: $prevCogs, $prevTotalQty, and $prevTotalValue.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('recalculating inventory');

        \DB::beginTransaction();

        /**
         * FIX INVENTORY COGS != JOURNAL
         */

        // // INVOICE
        // $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
        //     ->where('formulir.formulirable_type', '=', 'Point\PointSales\Models\Sales\Invoice')
        //     ->select('inventory.*')
        //     ->get();

        // foreach($inventories as $inventory) {
        //     // where('coa_id', '=', 385) => HPP
        //     $journal = Journal::where('form_journal_id', '=', $inventory->formulir_id)
        //         ->where('journal.subledger_id', '=', $inventory->item_id)
        //         ->where('journal.subledger_type', '=', "Point\Framework\Models\Master\Item")
        //         ->select('journal.*')
        //         ->first();

        //     // where('coa_id', '=', 385) => HPP
        //     $jHpp = Journal::where('coa_id', '=', 385)
        //         ->where('form_journal_id', '=', $inventory->formulir_id)
        //         ->select('journal.*')
        //         ->first();

        //     if (!$journal) {
        //         $this->comment('Journal not found | inventory_id: ' . $inventory->id . ' | formulir_id: ' . $inventory->formulir_id);
        //         continue;
        //     }


        //     $jValue = round(abs($journal->debit + $journal->credit), 4);
        //     $iValue = round(abs($inventory->quantity * $inventory->price), 4);

        //     if ($iValue !== $jValue) {
        //         $this->comment($journal->formulir->form_number . ' = ' . $inventory->id . ' | ' . $jValue . ' = ' . $iValue);
        //     }
            
        //     if ($journal->debit > 0) {
        //         $journal->debit = $iValue;
        //     } else {
        //         $journal->credit = $iValue;
        //     }
        //     $journal->save();
            
        //     if ($jHpp->debit > 0) {
        //         $jHpp->debit = $iValue;
        //     } else {
        //         $jHpp->credit = $iValue;
        //     }
        //     $jHpp->save();
        // }

        // // TI
        // $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
        //     ->where('formulir.formulirable_type', '=', 'Point\PointInventory\Models\TransferItem\TransferItem')
        //     ->select('inventory.*')
        //     ->get();

        // foreach($inventories as $inventory) {
        //     $journals = Journal::where('form_journal_id', '=', $inventory->formulir_id)
        //         ->where('journal.subledger_id', '=', $inventory->item_id)
        //         ->where('journal.subledger_type', '=', "Point\Framework\Models\Master\Item")
        //         ->select('journal.*')
        //         ->get();

        //     if (!count($journals)) {
        //         $this->comment('Journal not found | inventory_id: ' . $inventory->id . ' | formulir_id: ' . $inventory->formulir_id);
        //         continue;
        //     }

        //     $iValue = round(abs($inventory->quantity * $inventory->price), 4);

        //     foreach ($journals as $journal) {
        //         $this->comment($journal->description);
        //         if ($journal->debit > 0) {
        //             $journal->debit = $iValue;
        //         } else {
        //             $journal->credit = $iValue;
        //         }
        //         $journal->save();
        //     }
        // }

        // // INPUT MANUFACTURE
        // $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
        //     ->where('formulir.formulirable_type', '=', 'Point\PointManufacture\Models\InputProcess')
        //     ->select('inventory.*')
        //     ->get();

        // foreach($inventories as $inventory) {
        //     $journals = Journal::where('form_journal_id', '=', $inventory->formulir_id)
        //         ->where('journal.subledger_id', '=', $inventory->item_id)
        //         ->where('journal.subledger_type', '=', "Point\Framework\Models\Master\Item")
        //         ->select('journal.*')
        //         ->get();

        //     if (!count($journals)) {
        //         $this->comment('Journal not found | inventory_id: ' . $inventory->id . ' | formulir_id: ' . $inventory->formulir_id);
        //         continue;
        //     }

        //     $iValue = round(abs($inventory->quantity * $inventory->price), 4);

        //     foreach ($journals as $journal) {
        //         $this->comment($journal->description);
        //         if ($journal->debit > 0) {
        //             $journal->debit = $iValue;
        //         } else {
        //             $journal->credit = $iValue;
        //         }
        //         $journal->save();
        //     }
        // }

        // SC
        $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('formulir.formulirable_type', '=', 'Point\PointInventory\Models\StockCorrection\StockCorrection')
            ->select('inventory.*')
            ->get();

        foreach($inventories as $inventory) {
            $journals = Journal::where('form_journal_id', '=', $inventory->formulir_id)
                ->where('journal.subledger_id', '=', $inventory->item_id)
                ->where('journal.subledger_type', '=', "Point\Framework\Models\Master\Item")
                ->select('journal.*')
                ->get();

            if (!count($journals)) {
                $this->comment('Journal not found | inventory_id: ' . $inventory->id . ' | formulir_id: ' . $inventory->formulir_id);
                continue;
            }

            $iValue = round(abs($inventory->quantity * $inventory->price), 4);

            foreach ($journals as $journal) {
                $this->comment($journal->description);
                if ($journal->debit > 0) {
                    $journal->debit = $iValue;
                } else {
                    $journal->credit = $iValue;
                }
                $journal->save();
            }
        }

        /**
         * FIX OUTPUT SELISIH KOMA
         */

        // $journals = Journal::join('coa', 'coa.id', '=', 'journal.coa_id')
        //     ->join('formulir', 'formulir.id', '=', 'journal.form_journal_id')
        //     ->where('formulir.formulirable_type', '=', 'Point\PointManufacture\Models\OutputProcess')
        //     ->where('journal.debit', '>', 0)
        //     ->select('journal.*')
        //     ->get();

        // foreach($journals as $journal) {
        //     $inventory = Inventory::where('formulir_id', '=', $journal->form_journal_id)
        //         ->where('item_id', '=', $journal->subledger_id)
        //         ->first();

        //     $a = $inventory->price * $inventory->quantity;
        //     $b = $journal->debit;
                
        //     if ($a !== $b) {
        //         $c = $b - $a;
        //         $this->comment($journal->id . ' & ' . $journal->form_journal_id . ' = ' . $a . ' = ' . $b . ' = ' . ($b - $a));
    
        //         $j = new Journal();
        //         $j->form_date = $journal->form_date;
        //         $j->coa_id = $journal->coa_id;
        //         $j->description = 'Pembulatan';
        //         if ($c > 0) {
        //             $j->debit = 0;
        //             $j->credit = abs($c);
        //         } else {
        //             $j->debit = abs($c);
        //             $j->credit = 0;
        //         }
        //         $j->form_journal_id = $journal->form_journal_id;
        //         $j->form_reference_id = $journal->form_reference_id;
        //         $j->subledger_id = $journal->subledger_id;
        //         $j->subledger_type = $journal->subledger_type;
        //         $j->save();
                
        //         $j = new Journal();
        //         $j->form_date = $journal->form_date;
        //         $j->coa_id = 472;
        //         $j->description = 'Pembulatan';
        //         if ($c > 0) {
        //             $j->debit = abs($c);
        //             $j->credit = 0;
        //         } else {
        //             $j->debit = 0;
        //             $j->credit = abs($c);
        //         }
        //         $j->form_journal_id = $journal->form_journal_id;
        //         $j->form_reference_id = $journal->form_reference_id;
        //         $j->subledger_id = $journal->subledger_id;
        //         $j->subledger_type = $journal->subledger_type;
        //         $j->save();
        //     }
        // }

        \DB::commit(); 
    }
}