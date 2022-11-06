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

            $total_quantity = 0;
            $total_value = 0;
            $cogs = 0;
            $cogs_tmp = 0;

            foreach ($list_inventory as $index => $l_inventory) {
                if ($l_inventory->formulir->formulirable_type === StockOpname::class && $index > 0) {
                    $this->updateQuantityOpname($l_inventory, $index, $total_quantity, $total_value, $cogs, $cogs_tmp);
                } else {
                    $this->updateQuantityNonOpname($l_inventory, $index, $total_quantity, $total_value, $cogs, $cogs_tmp);
                }
            }
        }

        \DB::commit();
    }

    private function updateQuantityOpname($l_inventory, $index, $total_quantity, $total_value, $cogs, $cogs_tmp) {
        // UPDATE QUANTITY IF FORMULIR TYPE IS STOCKOPNAME
        $stockOpnameItem = StockOpname::join('point_inventory_stock_opname_item', 'point_inventory_stock_opname.id', '=', 'point_inventory_stock_opname_item.stock_opname_id')
            ->where('point_inventory_stock_opname.formulir_id', $l_inventory->formulir_id)
            ->where('point_inventory_stock_opname_item.item_id', $l_inventory->item_id)
            ->select('point_inventory_stock_opname_item.*')
            ->first();

        // INVENTORY BEFORE STOCK OPNAME
        $lastStock = Inventory::where('item_id', $l_inventory->item_id)
            ->where('warehouse_id', $l_inventory->warehouse_id)
            ->where('form_date', '<', $l_inventory->form_date)
            ->orderBy('form_date', 'desc')
            ->orderBy('formulir_id', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // REPLACE STOCK IN DATABASE WITH CORRECT VALUES
        if ($lastStock && $stockOpnameItem->item_id == $l_inventory->item_id) {
            $sti = StockOpnameItem::where("id", $stockOpnameItem->id)->first();
            $sti->stock_in_database = $lastStock->total_quantity;
            $sti->save();
        }

        // UPDATE INVENTORIES TABLE
        $total_quantity = $stockOpnameItem->quantity_opname;
        $l_inventory->total_quantity = $total_quantity;
        $l_inventory->quantity = $total_quantity - $list_inventory[$index-1]->total_quantity;
        $l_inventory->save();
    }

    private function updateQuantityNonOpname($l_inventory, $index, $total_quantity, $total_value, $cogs, $cogs_tmp) {
        // UPDATE TOTAL QUANTITY IF FORMULIR TYPE IS NOT STOCKOPNAME
        if ($index == 0) {
            $inv = Inventory::where('inventory.item_id', $l_inventory->item_id)
                ->where('form_date', '<', $l_inventory->form_date)
                ->where('warehouse_id', $l_inventory->warehouse_id)
                ->orderBy('form_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $total_quantity = $inv ? $inv->total_quantity : 0;
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
