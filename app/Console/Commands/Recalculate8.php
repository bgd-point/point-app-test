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

class Recalculate8 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:8';

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

        $items = Item::where('id', 741)->get();
        // $items = Item::all();

        foreach ($items as $item) {
            $inventory = Inventory::where('item_id', '=', $item->id)
                ->where('form_date', '>=', '2026-05-05')
                ->orderBy('form_date', 'desc')
                ->orderBy('formulir_id', 'desc')
                ->first();
            if (!$inventory) {
                continue;
            }

            $list_inventory = Inventory::where('item_id', '=', $item->id)
                ->where('form_date', '>=', $inventory->form_date)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();
            dd($item->name, $inventory->form_date, $list_inventory->count());
            foreach($list_inventory as $index => $l_inventory) {
                $this->comment('inventory id = ' . $l_inventory->formulir->form_number);
                $journals = Journal::where('form_journal_id', '=', $l_inventory->formulir_id)
                    ->where('subledger_id', '=', $item->id)
                    ->get();

                foreach ($journals as $journal) {
                    $this->comment('journal id = ' . $journal->id);
                }

                $prevTotalQty = $l_inventory->total_quantity_all;
                $prevTotalVal = $l_inventory->total_value_all;
            }
        }
    }
}