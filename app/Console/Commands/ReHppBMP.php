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

class ReHppBMP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:rehpp:bmp';

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
                return $inventory['item_id'].$inventory['warehouse_id'];
            });

        foreach ($inventories as $inventory) {

            $list_inventory = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->where('form_date', '>', '2026-06-30')
                ->orderBy('form_date', 'asc')
                ->get();

            $prev = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->where('form_date', '<=', '2026-06-30')
                ->orderBy('form_date', 'desc')
                ->orderBy('formulir_id', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            
            $prevAll = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
                ->where('form_date', '<=', '2026-06-30')
                ->orderBy('form_date', 'desc')
                ->orderBy('formulir_id', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $totalQty = 0;
            $totalQtyAll = 0;
            $totalValue = 0;
            $totalValAll = 0;
            $cogs = 0;
            foreach($list_inventory as $index => $l_inventory) {
                
                $this->comment($l_inventory->formulir->form_number);

                if ($index === 0) {
                    $totalQty = $prev ? $prev->total_quantity : 0;
                    $totalVal = $prev ? $prev->total_value : 0;
                    $totalQtyAll = $prevAll ? $prevAll->total_quantity_all : 0;
                    $totalValAll = $prevAll ? $prevAll->total_value_all : 0;
                }

                $value = $l_inventory->quantity * $l_inventory->price;

                $totalQty = $totalQty + $l_inventory->quantity;
                $totalQtyAll = $totalQtyAll + $l_inventory->quantity;
                
                $totalValue = round($totalValue + $value, 4);
                $totalValAll = round($totalValAll + $value, 4);

                

                if ($l_inventory->quantity > 0) {
                    $l_inventory->cogs = $totalValAll / $totalQtyAll;
                    $cogs = $l_inventory->cogs;
                } else {
                    $l_inventory->cogs = $cogs;
                }
                $l_inventory->recalculate = 0;
                $l_inventory->total_quantity = $totalQty;
                $l_inventory->total_quantity_all = $totalQtyAll;
                $l_inventory->total_value = $totalValue;
                $l_inventory->total_value_all = $totalValAll;
                $l_inventory->save();
            }
        }

        \DB::commit();
    }
}