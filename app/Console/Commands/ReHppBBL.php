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
            ->where('item_id', 610)
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'].$inventory['warehouse_id'];
            });

        foreach ($inventories as $inventory) {

            $list_inventory = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->orderBy('form_date', 'asc')
                ->get();

            $totalQty = 0;
            $totalValue = 0;
            $cogs = 0;
            foreach($list_inventory as $index => $l_inventory) {

                $value = $l_inventory->quantity * $l_inventory->price;

                $totalQty = $totalQty + $l_inventory->quantity;
                $totalValue = round($totalValue + $value, 4);

                $this->comment($l_inventory->formulir->form_number . ' ' . $value . ' + ' . $l_inventory->total_value . ' = ' . $totalValue);
                $this->comment($l_inventory->formulir->form_number . ' ' . $totalValue . ' / ' . $l_inventory->total_quantity . ' = ' . $totalValue / $l_inventory->total_quantity);
                if ($l_inventory->quantity > 0) {
                    $l_inventory->cogs = $totalValue / $l_inventory->total_quantity;
                    $cogs = $l_inventory->cogs;
                } else {
                    $l_inventory->cogs = $cogs;
                }
                $l_inventory->recalculate = 0;
                $l_inventory->total_value = $totalValue;
                $l_inventory->save();
            }
        }

        \DB::commit();
    }
}