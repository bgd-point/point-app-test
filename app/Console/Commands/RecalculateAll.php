<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Retur;
use Point\Framework\Models\Master\Warehouse;

class RecalculateAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:all';

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

        $items = Item::all();
        $inventories = Inventory::all();
        $warehouses = Warehouse::all();

        $i = 0;
        foreach ($items as $item) {
            $i++;
            \DB::beginTransaction();
            
            $list_inventory = Inventory::where('item_id', '=', $item->id)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            $prevTotalQty = 0;
            $prevTotalVal = 0;
            $this->comment('I' . count($items) . ' = ' . $i);
            foreach($list_inventory as $index => $l_inventory) {
                $l_inventory->total_quantity_all = $prevTotalQty + $l_inventory->quantity;
                $l_inventory->total_value_all = $prevTotalVal + ($l_inventory->quantity * $l_inventory->price);
                if (!$l_inventory->total_quantity_all || $l_inventory->total_quantity_all == 0) {
                    $l_inventory->cogs = 0;
                } else {
                    $l_inventory->cogs = $l_inventory->total_value_all / $l_inventory->total_quantity_all;
                }
                $l_inventory->save();

                $prevTotalQty = $l_inventory->total_quantity_all;
                $prevTotalVal = $l_inventory->total_value_all;
            }

            // $k = 0;
            // foreach ($warehouses as $warehouse) {
            //     $k++;
            //     $list_inventory = Inventory::where('item_id', '=', $inventory->item_id)
            //         ->where('warehouse_id', $warehouse->id)
            //         ->orderBy('form_date', 'asc')
            //         ->orderBy('formulir_id', 'asc')
            //         ->get();

            //     $prevTotalQty = 0;
            //     $prevTotalVal = 0;

            //     $j = 0;
            //     foreach($list_inventory as $index => $l_inventory) {
            //         $j++;
            //         $this->comment('I' . count($inventories) . ' = ' . $i . ' | W' . count($warehouses) . ' = ' . $k . ' | J' . count($list_inventory) . ' = ' . $j);
            //         $l_inventory->total_quantity = $prevTotalQty + $l_inventory->quantity;
            //         $l_inventory->total_value = $prevTotalVal + ($l_inventory->quantity * $l_inventory->price);
            //         $l_inventory->save();

            //         $prevTotalQty = $l_inventory->total_quantity;
            //         $prevTotalVal = $l_inventory->total_value;
            //     }
            // }

        }
        
        \DB::commit();
    }
}