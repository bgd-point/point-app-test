<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;

class ReHppBBL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:rehpp:bbl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'recalculate hpp';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('recalculating hpp');

        \DB::beginTransaction();

        // Get all items
        $inventories = Inventory::orderBy('form_date', 'asc')
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'];
            });

        foreach ($inventories as $inventory) {

            $totalQ = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
		        ->where('form_date', '<', '2025-06-01')
                ->sum('quantity');

            $journal = Journal::where('subledger_type', 'Point\Framework\Models\Master\Item')
                ->where('subledger_id', $inventory->item_id)
                ->where('form_date', '<', '2025-06-01')
                ->selectRaw('sum(debit) as debit, sum(credit) as credit, count(coa_id) as counter')
                ->first();

            $totalV = $journal->debit - $journal->credit;

            $list_inventory = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
		        ->where('form_date', '<', '2025-06-01')
                ->orderBy('form_date', 'asc')
                ->get();

            if ($totalV < 0 ||  $totalQ < 0) {
                $this->comment('problem item ' . $inventory->item_id);
                continue;
            }

            $hpp = $totalV / $totalQ;

            $totalQty = 0;
            $totalValue = 0;
            foreach($list_inventory as $index => $l_inventory) {

                $value = abs($l_inventory->quantity) * $hpp;
                $totalValue = $totalValue + $value;

                $l_inventory->recalculate = 0;
                $l_inventory->cogs = $hpp;
                $l_inventory->total_value = $totalValue;
                $l_inventory->save();
            }
        }

        \DB::commit();
    }
}