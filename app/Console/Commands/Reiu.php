<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\TransferItem\TransferItem;

class Reiu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:reiu';

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

        $usages = InventoryUsage::join('formulir', 'formulir.id', '=', 'point_inventory_usage.formulir_id')
            ->where('formulir.form_date', '>=', '2019-10-01')
            ->where('formulir.form_status', '>=', 1)
            ->whereNotNull('formulir.form_number')
            ->orderBy('formulir.form_date', 'asc')
            ->select('point_inventory_usage.*')
            ->get();

        foreach($usages as $usage) {
            foreach ($usage->listInventoryUsage as $usageItem) {
                $inv = Inventory::where('inventory.item_id', $usageItem->item_id)
                    ->where('form_date', '<', $usage->formulir->form_date)
                    ->where('warehouse_id', $usage->warehouse_id)
                    ->orderBy('form_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $stock = 0;

                if ($inv) {
                    $this->line('TOTAL QUANTITY ' . $inv->total_quantity);
                    $stock = $inv->total_quantity;
                }

                $usageItem->stock_in_database = $stock;
                $usageItem->save();
            }

            Inventory::where('formulir_id', $usage->formulir_id)->delete();

            foreach ($usage->listInventoryUsage as $usageItem) {
                $inventory = new Inventory;
                $inventory->form_date = $usage->formulir->form_date;
                $inventory->formulir_id = $usage->formulir_id;
                $inventory->warehouse_id = $usage->warehouse_id;
                $inventory->item_id = $usageItem->item_id;
                $inventory->price =  InventoryHelper::getCostOfSales($usage->formulir->form_date, $usageItem->item_id, $usage->warehouse_id);
                $quantity = $usageItem->quantity_usage * -1;
                if ($quantity < 0) {
                    $inventory->quantity = $quantity * -1;
                    $inventory_helper = new InventoryHelper($inventory);
                    $inventory_helper->out();
                } elseif ($quantity > 0) {
                    $inventory->quantity = $quantity;
                    $inventory_helper = new InventoryHelper($inventory);
                    $inventory_helper->in();
                } else {
                    continue;
                }
            }
        }

        \DB::commit();
    }
}
