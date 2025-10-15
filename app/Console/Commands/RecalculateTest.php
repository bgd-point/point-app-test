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

        $list_inventory = Inventory::with('formulir')
            ->where('inventory.item_id', 102)
            ->where('inventory.warehouse_id', 1)
            ->where('inventory.form_date', '>=','2025-08-01')
            ->where('inventory.form_date', '<','2025-09-01')
            ->orderBy('form_date', 'asc')
            ->orderBy('formulir_id', 'asc')
            ->get();

        $prevCogs = 0;
        $prevTotalQty = 0;
        $prevTotalValue = 0;

        foreach($list_inventory as $index => $l_inventory) {
            if ($index == 0) {
                $l_inventory->total_quantity = (float) $l_inventory->quantity;
                $l_inventory->total_value = (float) $l_inventory->quantity * (float) $l_inventory->price;
                $l_inventory->cogs = (float) $l_inventory->total_value / (float) $l_inventory->total_quantity;
            } else {
                $l_inventory->total_quantity = (float) $l_inventory->quantity + $prevTotalQty;
                $l_inventory->total_value = (float) $l_inventory->quantity * (float) $l_inventory->price;
                $l_inventory->cogs = (float) $l_inventory->total_value / (float) $l_inventory->total_quantity;
            }

            $prevCogs = (float) $l_inventory->cogs;
            $prevTotalQty = (float) $l_inventory->total_quantity;
            $prevTotalValue = (float) $l_inventory->total_value;

            if ((float) $l_inventory->total_quantity <= 0) {
                $l_inventory->total_value = 0;
            }

            if ((float) $l_inventory->total_quantity < 0) {
                $l_inventory->recalculate = 1;
            }

            $l_inventory->save();

            $this->comment($l_inventory->form_date . ' = ' . $l_inventory->cogs);
        }

        \DB::commit();
    }
}