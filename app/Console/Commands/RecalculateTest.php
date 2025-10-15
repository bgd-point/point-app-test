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
use Point\PointSales\Models\Sales\Retur;

class RecalculateTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:test';

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
        // Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
        //     ->where('inventory.quantity', 0)
        //     ->where('formulir.formulirable_type', '!=', 'Point\PointInventory\Models\StockOpname\StockOpname')
        //     ->where('inventory.item_id', 102)
        //     ->where('inventory.warehouse_id', 1)
        //     ->where('inventory.form_date', '>=','2025-08-01')
        //     ->where('inventory.form_date', '<','2025-09-01')
        //     ->delete();

        $list_inventory = Inventory::with('formulir')
            ->where('inventory.item_id', 102)
            ->where('inventory.warehouse_id', 1)
            ->where('inventory.form_date', '>=','2025-08-01')
            ->where('inventory.form_date', '<','2025-09-01')
            ->orderBy('form_date', 'asc')
            ->orderBy('formulir_id', 'asc')
            ->get();

        $prevCogs = 0;
        $prevTotal = 0;
        foreach($list_inventory as $index => $l_inventory) {
            if ($index == 0) {
                $l_inventory->total_quantity = $l_inventory->quantity;
                $totalQty = (float) $l_inventory->total_quantity;
                $totalValue = $l_inventory->quantity * $l_inventory->price;
                $l_inventory->recalculate = 0;
                
                if ((float) $l_inventory->cogs == 0) {
                    $l_inventory->cogs = $cogs;
                } else {
                    $cogs = $l_inventory->cogs;
                }
                $l_inventory->total_value = $totalValue;
                // $l_inventory->save();
                $prevCogs = $l_inventory->cogs;
                $prevTotal = $l_inventory->total_value;
            } else if ($l_inventory->formulir->formulirable_type === StockOpname::class) {
                $l_inventory->recalculate = 0;
                if ((float) $l_inventory->quantity < 0 || $l_inventory->formulir->formulirable_type === Retur::class) {
                    $l_inventory->price = $prevCogs;
                    $l_inventory->cogs = $prevCogs;
                }
                if ((float) $l_inventory->cogs == 0) {
                    $l_inventory->cogs = $cogs;
                } else {
                    $cogs = $l_inventory->cogs;
                }
                $l_inventory->total_value = $l_inventory->cogs * $l_inventory->total_quantity;
                // $l_inventory->save();
                $totalQty = (float) $l_inventory->total_quantity;
                $prevCogs = $l_inventory->cogs;
                $prevTotal = $l_inventory->total_value;
            } else {
                $l_inventory->recalculate = 0;
                // if value 0 from output
                if ($l_inventory->price == 0) {
                    $l_inventory->price = $cogs;
                }
                $l_inventory->total_quantity = (float) $totalQty + (float) $l_inventory->quantity;
                $l_inventory->total_value = $totalValue + ($l_inventory->quantity * $l_inventory->price);
                if ((float) $l_inventory->quantity < 0  || $l_inventory->formulir->formulirable_type === Retur::class) {
                    $l_inventory->price = $prevCogs;
                    $l_inventory->cogs = $prevCogs;
                }
                if ((float) $l_inventory->cogs == 0) {
                    $l_inventory->cogs = $cogs;
                } else {
                    $cogs = $l_inventory->cogs;
                }
                $l_inventory->total_value = $l_inventory->cogs * $l_inventory->total_quantity;
                // $l_inventory->save();
                $l_inventory->cogs = ($prevTotal + ($l_inventory->quantity * $l_inventory->price)) / $l_inventory->total_quantity;
                $this->comment($l_inventory->id . ' = ' . $l_inventory->cogs);

                $totalQty = (float) $l_inventory->total_quantity;
                $totalValue = $l_inventory->total_value;
                $prevCogs = $l_inventory->cogs;
                $prevTotal = $l_inventory->total_value;
            }

            if ((float) $l_inventory->total_quantity <= 0) {
                $l_inventory->total_value = 0;

                if ((float) $l_inventory->total_quantity < 0) {
                    $l_inventory->recalculate = 1;
                }

                // $l_inventory->save();
            }
        }

        \DB::commit();
    }
}