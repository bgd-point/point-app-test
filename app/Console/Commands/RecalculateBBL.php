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

class RecalculateBBL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:bbl';

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
		        ->where('form_date', '>=', '2024-01-01')
                ->orderBy('form_date', 'asc')
                ->orderBy('quantity', 'desc')
                ->get();

            $totalQty = 0;
            $totalValue = 0;
            $cogs = 0;
            foreach($list_inventory as $index => $l_inventory) {
                if ($index === 0) {
                    $l_inventory->total_quantity = $l_inventory->quantity;
                    $totalQty = $l_inventory->total_quantity;
                    $totalValue = $l_inventory->quantity * $l_inventory->price;
                    $l_inventory->recalculate = 0;
                    if ($l_inventory->cogs === 0) {
                        $l_inventory->cogs = $cogs;
                    } else {
                        $cogs = $l_inventory->cogs;
                    }
                    $l_inventory->total_value = $totalValue;
                    $l_inventory->save();
                } else if ($l_inventory->formulir->formulirable_type === StockOpname::class) {
                    $l_inventory->recalculate = 0;
                    if ($l_inventory->cogs === 0) {
                        $l_inventory->cogs = $cogs;
                    } else {
                        $cogs = $l_inventory->cogs;
                    }
                    $l_inventory->total_value = $l_inventory->cogs * $l_inventory->total_quantity;
                    $l_inventory->save();
                    $totalQty = $l_inventory->total_quantity;
                } else {
                    $l_inventory->recalculate = 0;
                    $l_inventory->total_quantity = $totalQty + $l_inventory->quantity;
                    $l_inventory->total_value = $totalValue + ($l_inventory->quantity * $l_inventory->price);
                    if ($l_inventory->cogs === 0) {
                        $l_inventory->cogs = $cogs;
                    } else {
                        $cogs = $l_inventory->cogs;
                    }
                    $l_inventory->total_value = $l_inventory->cogs * $l_inventory->total_quantity;
                    $l_inventory->save();
                    $totalQty = $l_inventory->total_quantity;
                    $totalValue = $l_inventory->total_value;
                }

                // value = 0, if total qty = 0
                if ($l_inventory->total_quantity <= 0) {
                    $l_inventory->total_value = 0;

                    if ($l_inventory->total_quantity < 0) {
                        $l_inventory->recalculate = 1;
                    }

                    $l_inventory->save();
                }

                if ($l_inventory->item_id === 661) {
                    $this->comment($l_inventory->id);
                    $this->comment('=');
                    $this->comment((float) $l_inventory->cogs);
                    $this->comment((float) $l_inventory->cogs === 0);
                    $this->comment(floatVal($l_inventory->cogs) == 0);
                }
            }
        }

        \DB::commit();
    }
}