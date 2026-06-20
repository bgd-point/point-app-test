<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Retur;
use Point\Framework\Models\Master\Warehouse;

class Recalculate7 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:7';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'recalculate inventory';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('handle inventory all');

        // $items = Item::where('id', 678)->get();
        $items = Item::all();

        foreach ($items as $item) {
            $inventory = Inventory::where('item_id', '=', $item->id)
                ->where('form_date', '>=', '2026-06-05')
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->first();
            if (!$inventory) {
                continue;
            }

            $list_inventory = Inventory::where('item_id', '=', $item->id)
                ->where('form_date', '>=', $inventory->form_date)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            $prevTotalQty = 0;
            $prevTotalVal = 0;

            foreach($list_inventory as $index => $l_inventory) {
                $journals = Journal::where('form_journal_id', '=', $l_inventory->formulir_id)
                    ->where('subledger_id', '=', $item->id)
                    ->get();
                echo $l_inventory->formulir->form_number . ' = ' . $l_inventory->price . ' / ' . $l_inventory->quantity . ' / ' . $l_inventory->total_value . "\n";
                foreach ($journals as $journal) {
                    if ($journal->debit > 0) {
                        $journal->debit = abs($l_inventory->price * $l_inventory->quantity);
                    }
                    if ($journal->credit > 0) {
                        $journal->credit = abs($l_inventory->price * $l_inventory->quantity);
                    }
                    $journal->save();

                    // update stock correction journal
                    if ($journal->formulir->formulirable_type === 'Point\PointInventory\Models\StockCorrection\StockCorrection') {
                        $j = Journal::where('id', '=', $journal->id + 1)->first();

                        if ($j->debit > 0) {
                            $j->debit = abs($l_inventory->price * $l_inventory->quantity);
                        }
                        if ($j->credit > 0) {
                            $j->credit = abs($l_inventory->price * $l_inventory->quantity);
                        }
                        $j->save();
                    }
                    
                    // update IU journal
                    if ($journal->formulir->formulirable_type === 'Point\PointInventory\Models\InventoryUsage\InventoryUsage') {
                        $j = Journal::where('id', '=', $journal->id + 1)->first();

                        if ($j->debit > 0) {
                            $j->debit = abs($l_inventory->price * $l_inventory->quantity);
                        }
                        if ($j->credit > 0) {
                            $j->credit = abs($l_inventory->price * $l_inventory->quantity);
                        }
                        $j->save();
                    }

                    // update Purchase journal
                    if ($journal->formulir->formulirable_type === 'Point\PointPurchasing\Models\Inventory\Invoice') {
                        // if ($journal->form_journal_id === 3293) {
                        //     // TODO: should fix inventory table using this value
                        //     Journal::where('id', 5814)->update(['debit' => 200900.00]);
                        // }

                        // if ($journal->form_journal_id === 3973) {
                        //     Journal::where('id', 6966)->update(['debit' => 27477477.48]);
                        //     Journal::where('id', 6969)->delete();
                        // }
                    }

                    // if ($journal->formulir->formulirable_type === 'Point\PointSales\Models\Sales\Invoice') {
                    //     $jCogs = Journal::where('form_journal_id', $journal->form_journal_id)->where('coa_id', 385)->delete();
                    //     $js = Journal::where('form_journal_id', $journal->form_journal_id)->where('description', 'like', 'invoice "%')->get();

                    //     foreach ($js as $j) {
                    //         $cost_of_sales_account = JournalHelper::getAccount('point sales indirect', 'cost of sales');
                    //         $jp = new Journal;
                    //         $jp->form_date = $j->form_date;
                    //         $jp->coa_id = $cost_of_sales_account;
                    //         $jp->description = 'invoice indirect sales "' . $inventory->formulir->form_number.'"';
                    //         $jp->debit = $j->credit;
                    //         $jp->form_journal_id = $j->form_journal_id;
                    //         $jp->form_reference_id;
                    //         $jp->subledger_id;
                    //         $jp->subledger_type;
                    //         $jp->save();
                    //     }
                    // }

                    if ($journal->formulir->formulirable_type === 'Point\PointManufacture\Models\OutputProcess') {
                        Journal::where('form_journal_id', $journal->form_journal_id)->where('coa_id', 509)->delete();
                        $jDebit = Journal::where('form_journal_id', $journal->form_journal_id)->where('debit', '>', 0)->first();
                        $jCredit = Journal::where('form_journal_id', $journal->form_journal_id)->where('credit', '>', 0)->sum('credit');

                        $price = $jCredit / $l_inventory->quantity;

                        $jDebit->debit = ($l_inventory->price * $l_inventory->quantity);
                        $jDebit->save();

                        $l_inventory->price = $price;
                        $l_inventory->total_value_all = $prevTotalVal + ($l_inventory->price * $l_inventory->quantity);
                        $l_inventory->save();

                        $this->comment('Output Process ' . $journal->formulir->form_number . ' - ' . $l_inventory->price);
                    }
                }

                $prevTotalQty = $l_inventory->total_quantity_all;
                $prevTotalVal = $l_inventory->total_value_all;
            }
        }
    }
}