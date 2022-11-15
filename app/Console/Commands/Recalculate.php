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

class Recalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate';

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
        $this->comment('recalculating inventory');

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
                ->where('form_date', '>=', '2022-01-01')
                ->orderBy('form_date', 'asc')
                ->get();
            
            $opnameItem = StockOpnameItem::join('point_inventory_stock_opname', 'point_inventory_stock_opname_item.stock_opname_id', '=', 'point_inventory_stock_opname.id')
                ->join('formulir', 'formulir.id', '=', 'point_inventory_stock_opname.formulir_id')
                ->where('point_inventory_stock_opname_item.item_id', '=', $inventory->item_id)
                ->where('point_inventory_stock_opname.warehouse_id', '=', $inventory->warehouse_id)
                ->where('formulir.form_date', '<=', $inventory->form_date)
                ->where('formulir.form_status', '>=', 0)
                ->where('formulir.approval_status', '>', 0)
                ->whereNotNull('formulir.form_number')
                ->orderBy('formulir.form_date', 'desc')
                ->select('point_inventory_stock_opname_item.*')
                ->first();
            
            if ($opname) {
                $list_inventory = Inventory::with('formulir')
                    ->where('item_id', '=', $inventory->item_id)
                    ->where('warehouse_id', '=', $inventory->warehouse_id)
                    ->where('form_date', '>', $opnameItem->opname->formulir->form_date)
                    ->orderBy('form_date', 'asc')
                    ->get();
            }

            $total_quantity = 0;
            $total_value = 0;
            $cogs = 0;
            $cogs_tmp = 0;

            foreach ($list_inventory as $index => $l_inventory) {
                $this->line($index);
                             
                if ($index == 0) {
                    $inv = Inventory::where('inventory.item_id', $l_inventory->item_id)
                        ->where('form_date', '<', $l_inventory->form_date)
                        ->where('warehouse_id', $l_inventory->warehouse_id)
                        ->orderBy('form_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    if ($l_inventory->item_id == 347) {
                        $this->line('inv ' . $inv);
                    }


                    if ($inv) {
                        $this->line($inv->total_quantity . ' = ' . $l_inventory->quantity);
                        $total_quantity = $inv->total_quantity;
                    } else {
                        $total_quantity = 0;
                    }
                }

                $total_quantity += $l_inventory->quantity;

                if ($l_inventory->quantity > 0) {
                    // STOCK IN
                    if ($total_quantity <= 0) {
                        // IGNORE VALUE BECAUSE USER ERROR (STOCK MINUS)
                        $l_inventory->recalculate = true;
                        $total_value = 0;
                        $cogs = 0;
                    } else {
                        $total_value += ($l_inventory->quantity * $l_inventory->price);
                        $cogs = $total_value / $total_quantity;
                    }
                    $l_inventory->cogs = $cogs;
                } else {
                    // STOCK OUT
                    if ($total_quantity < 0) {
                        // STOCK MINUS = NEED FIX FROM USER
                        $l_inventory->recalculate = true;
                        $l_inventory->cogs = $cogs;
                        $total_value = 0;
                    } else {
                        $total_value += ($l_inventory->quantity * $cogs);
                        $l_inventory->cogs = $cogs;
                    }
                }

                $l_inventory->total_quantity = $total_quantity;
                $l_inventory->total_value = $total_value;
                $l_inventory->save();
            }
        }

        \DB::commit();
    }
}
