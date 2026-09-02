<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Retur;
use Point\Framework\Models\Master\Warehouse;

class RecalculateAug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:aug';

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

        $inventories = Inventory::orderBy('form_date', 'asc')
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'].$inventory['warehouse_id'];
            });

        $this->comment('Found ' . count($inventories) . ' unique item_id and warehouse_id combinations');

        foreach ($inventories as $inventory) {
            // $this->comment('Processing item_id: ' . $inventory->item_id . ', warehouse_id: ' . $inventory->warehouse_id);
            $item_id = $inventory->item_id;
            $warehouse_id = $inventory->warehouse_id;

            $last = Inventory::where('item_id', '=', $item_id)
                ->where('warehouse_id', '=', $warehouse_id)
                ->where('form_date', '<', '2026-08-01')
                ->orderBy('form_date', 'desc')
                ->orderBy('formulir_id', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            
            if (!$last) {
                $last = Inventory::where('item_id', '=', $item_id)
                    ->where('warehouse_id', '=', $warehouse_id)
                    ->orderBy('form_date', 'asc')
                    ->orderBy('formulir_id', 'asc')
                    ->orderBy('id', 'asc')
                    ->first();

                if (!$last) {
                    $this->comment('No inventory records found for item_id: ' . $item_id . ', warehouse_id: ' . $warehouse_id);
                    continue;
                }
            }

            $list_inventory = Inventory::where('item_id', '=', $item_id)
                ->where('warehouse_id', '=', $warehouse_id)
                ->where('form_date', '>=', $last->form_date)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $this->comment('Found ' . count($list_inventory) . ' inventory records for item_id: ' . $item_id . ', warehouse_id: ' . $warehouse_id);

            foreach ($list_inventory as $index => $inv) {
                if ($index === 0) {
                    continue;
                }
                $this->comment('item_id = ' . $item_id . ' | warehouse_id = ' . $warehouse_id . ' | inventory_id = ' . $inv->form_date . ' | total_quantity = ' . $inv->total_quantity . ' | id = ' . $inv->id);
                $inv->total_quantity = $inv->quantity + $last->total_quantity;
                $inv->save();
            }
        }
        
        $inventories = Inventory::orderBy('form_date', 'asc')
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'];
            });

        foreach ($inventories as $inventory) {
            $item_id = $inventory->item_id;
            
            $lastAll = Inventory::where('item_id', '=', $item_id)
                ->where('form_date', '<', '2026-08-01')
                ->orderBy('form_date', 'desc')
                ->orderBy('formulir_id', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if (!$lastAll) {
                $lastAll = Inventory::where('item_id', '=', $item_id)
                    ->orderBy('form_date', 'asc')
                    ->orderBy('formulir_id', 'asc')
                    ->orderBy('id', 'asc')
                    ->first();
                    
                if (!$lastAll) {
                    echo 'continue' .PHP_EOL;
                    continue;
                }
            }

            $list_inventory = Inventory::where('item_id', '=', $item_id)
                ->where('form_date', '>=', $lastAll->form_date)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($list_inventory as $index => $inv) {
                if ($index === 0) {
                    continue;
                }
                $inv->total_quantity_all = $inv->quantity + $last->total_quantity_all;
                $inv->save();
            }
        }
    }
}