<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Retur;
use Point\PointSales\Models\Sales\Invoice;
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
class RecalculateHppFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:hppfix';

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
        // $coas = Coa::where('coa_category_id', 4)->get();
        // foreach($coas as $coa) {
        //     Journal::where('form_date', '<', '2026-08-01 00:00:00')
        //         ->where('coa_id', $coa->id)
        //         ->delete();
        // }
        // Journal::where('form_date', '<', '2026-08-01 00:00:00')
        //     ->where('coa_id', 45)
        //     ->delete();

        // INVOICE
        $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('formulir.formulirable_type', '=', 'Point\PointSales\Models\Sales\Invoice')
            ->where('inventory.form_date', '>=', '2026-08-01 00:00:00')
            ->select('inventory.*')
            ->get();

        foreach($inventories as $inventory) {
            $this->comment('$inventory' . $inventory->id);
            // where('coa_id', '=', 45) => HPP
            $journal = Journal::where('coa_id', '!=', 45)
                ->where('form_journal_id', '=', $inventory->formulir_id)
                ->where('journal.subledger_id', '=', $inventory->item_id)
                ->where('journal.subledger_type', '=', "Point\Framework\Models\Master\Item")
                ->select('journal.*')
                ->first();

            $journal->debit = 0;
            $journal->credit = $inventory->quantity * $inventory->price;
            
            $journal->save();

            // where('coa_id', '=', 45) => HPP
            $jHpp = Journal::where('coa_id', '=', 45)
                ->where('form_journal_id', '=', $inventory->formulir_id)
                ->where('id', '=', $journal->id + 1)
                ->select('journal.*')
                ->first();

            if (!$jHpp) {
                $this->comment($journal->id);
                dd('asds');
            }
            
            $jHpp->debit = $inventory->quantity * $inventory->price;
            $jHpp->credit = 0;
            
            $jHpp->save();
        }

        // // TI
        // $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
        //     ->where('formulir.formulirable_type', '=', 'Point\PointInventory\Models\TransferItem\TransferItem')
        //     ->where('inventory.form_date', '>=', '2026-08-01 00:00:00')
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

        // INPUT MANUFACTURE
        $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('formulir.formulirable_type', '=', 'Point\PointManufacture\Models\InputProcess')
            ->where('inventory.form_date', '>=', '2026-08-01 00:00:00')
            ->select('inventory.*')
            ->get();

        foreach($inventories as $inventory) {
            $this->comment('' . $inventory->formulir->form_number);
            $journals = Journal::where('form_journal_id', '=', $inventory->formulir_id)
                ->where('journal.subledger_id', '=', $inventory->item_id)
                ->where('journal.coa_id', '!=', 10)
                ->where('journal.subledger_type', '=', "Point\Framework\Models\Master\Item")
                ->select('journal.*')
                ->get();

            if (!count($journals)) {
                $this->comment('Journal not found | item_id: ' . $inventory->item_id . ' | formulir_id: ' . $inventory->formulir_id);
                continue;
            } else {
                $this->comment('Journal');
            }

            $iValue = round(abs($inventory->quantity * $inventory->price), 4);

            foreach ($journals as $journal) {
                $journal->debit = 0;
                $journal->credit = $iValue;
                $journal->save();
            }

            $journals = Journal::where('form_journal_id', '=', $inventory->formulir_id)
                ->where('journal.subledger_id', '=', $inventory->item_id)
                ->where('journal.coa_id', '=', 10)
                ->where('journal.subledger_type', '=', "Point\Framework\Models\Master\Item")
                ->select('journal.*')
                ->get();

            if (!count($journals)) {
                $this->comment('Journal not found | item_id: ' . $inventory->item_id . ' | formulir_id: ' . $inventory->formulir_id);
                continue;
            } else {
                $this->comment('Journal');
            }

            $iValue = round(abs($inventory->quantity * $inventory->price), 4);

            foreach ($journals as $journal) {
                $journal->debit = $iValue;
                $journal->credit = 0;
                $journal->save();
            }
        }

        // SC
        // $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
        //     ->where('formulir.formulirable_type', '=', 'Point\PointInventory\Models\StockCorrection\StockCorrection')
        //     ->where('inventory.form_date', '>=', '2026-07-01 00:00:00')
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

        //         $nextId = $journal->id + 1;
        //         $jHpp = Journal::where('id', '=', $nextId)
        //             ->where('form_journal_id', '=', $journal->form_journal_id)
        //             ->select('journal.*')
        //             ->first();

        //         if (!$jHpp) {
        //             $this->comment("Missing pair for ID {$journal->id}, expected {$nextId}");
        //             continue;
        //         }

        //         $this->comment($journal->formulir->form_number . ' = ' . $journal->id);
        //         if ($inventory->quantity > 0) {
        //             $journal->debit = $iValue;
        //             $journal->credit = 0;
        //             $jHpp->debit = 0;
        //             $jHpp->credit = $iValue;
        //         } else {
        //             $journal->debit = 0;
        //             $journal->credit = $iValue;
        //             $jHpp->debit = $iValue;
        //             $jHpp->credit = 0;
        //         }
        //         $journal->save();
        //         $jHpp->save();
        //     }
        // }

        // IU
        // $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
        //     ->where('formulir.formulirable_type', '=', 'Point\PointInventory\Models\InventoryUsage\InventoryUsage')
        //     ->where('inventory.form_date', '>=', '2026-08-01 00:00:00')
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
        //         $nextId = $journal->id + 1;
        //         $jHpp = Journal::where('debit', '>', 0)
        //             ->where('id', '=', $nextId)
        //             ->where('form_journal_id', '=', $journal->form_journal_id)
        //             ->select('journal.*')
        //             ->first();

        //         if (!$jHpp) {
        //             $this->comment("Missing pair for ID {$journal->id}, expected {$nextId}");
        //             continue;
        //         }

        //         $this->comment($journal->description . ' = ' . $journal->id);
        //         if ($journal->debit > 0) {
        //             $journal->debit = $iValue;
        //             $jHpp->credit = $iValue;
        //         } else {
        //             $journal->credit = $iValue;
        //             $jHpp->debit = $iValue;
        //         }
        //         $journal->save();
        //         $jHpp->save();
        //     }
        // }

        // /**
        //  * FIX OUTPUT SELISIH KOMA
        //  */
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

        /**
         * FIX JOURNAL RETUR
         */
        // $journals = Journal::join('coa', 'coa.id', '=', 'journal.coa_id')
        //     ->join('formulir', 'formulir.id', '=', 'journal.form_journal_id')
        //     ->where('formulir.formulirable_type', '=', 'Point\PointSales\Models\Sales\Retur')
        //     ->select('journal.*')
        //     ->groupBy('form_journal_id')
        //     ->get();
            
        // foreach($journals as $journal) {
        //     $this->comment($journal->formulir->form_number);
        //     $retur = Retur::where('formulir_id', '=', $journal->form_journal_id)->first();

        //     $inv = Invoice::where('id', $retur->point_sales_invoice_id)->first();
        //     $invJournals = Journal::where('form_journal_id', '=', $inv->formulir->id)
        //         ->select('journal.*')
        //         ->get();

        //     Journal::where('form_journal_id', $retur->formulir->id)->delete();

        //     foreach ($invJournals as $invJournal) { 
        //         $j = new Journal();
        //         $j->form_date = $retur->formulir->form_date;
        //         $j->coa_id = $invJournal->coa_id;
        //         $j->description = $invJournal->description;
        //         $j->debit = $invJournal->credit;
        //         $j->credit = $invJournal->debit;
        //         $j->form_journal_id = $retur->formulir->id;
        //         $j->form_reference_id = $invJournal->form_reference_id;
        //         $j->subledger_id = $invJournal->subledger_id;
        //         $j->subledger_type = $invJournal->subledger_type;
        //         $j->save();

        //         $this->comment($j);
        //     }
        // }

        // $returs = Retur::join('formulir', 'formulir.id', '=', 'point_sales_retur.formulir_id')
        //     ->where('formulir.form_status', '=', 0)
        //     ->get();

        // foreach($returs as $retur) {
        //     Journal::where('form_journal_id', $retur->formulir->id)->delete();
        //     $inv = Invoice::where('id', $retur->point_sales_invoice_id)->first();
        //     $invJournals = Journal::where('form_journal_id', '=', $inv->formulir->id)
        //         ->select('journal.*')
        //         ->get();

        //     foreach ($invJournals as $invJournal) { 
        //         $j = new Journal();
        //         $j->form_date = $retur->formulir->form_date;
        //         $j->coa_id = $invJournal->coa_id;
        //         $j->description = $invJournal->description;
        //         $j->debit = $invJournal->credit;
        //         $j->credit = $invJournal->debit;
        //         $j->form_journal_id = $retur->formulir->id;
        //         $j->form_reference_id = $invJournal->form_reference_id;
        //         $j->subledger_id = $invJournal->subledger_id;
        //         $j->subledger_type = $invJournal->subledger_type;
        //         $j->save();

        //         $this->comment($j);
        //     }
        // }

        \DB::commit(); 
    }
}