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
        Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('inventory.quantity', 0)
            ->where('formulir.formulirable_type', '!=', 'Point\PointInventory\Models\StockOpname\StockOpname')
            ->delete();
        
        $inventories = Inventory::orderBy('form_date', 'asc')
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'].$inventory['warehouse_id'];
            });

        foreach ($inventories as $inventory) {

            $list_inventory = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            $totalQty = 0;
            $totalValue = 0;
            $cogs = 0;
            foreach($list_inventory as $index => $l_inventory) {
                if ($l_inventory->formulir->formulirable_type === StockCorrection::class) {
                    \Log::info($l_inventory->formulir->id)
                    $l_inventory->form_date = $l_inventory->formulir->form_date;
                    $l_inventory->save();
                }
            }

            $list_inventory = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            foreach($list_inventory as $index => $l_inventory) {
                $this->comment('total = ' . $l_inventory->total_quantity . ' ' . (float) $l_inventory->quantity);
                if ($index == 0) {
                    $this->comment('if1');
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
                    $l_inventory->save();
                } else if ($l_inventory->formulir->formulirable_type === StockOpname::class) {
                    $this->comment('if2');
                    $l_inventory->recalculate = 0;
                    if ((float) $l_inventory->cogs == 0) {
                        $l_inventory->cogs = $cogs;
                    } else {
                        $cogs = $l_inventory->cogs;
                    }
                    $l_inventory->total_value = $l_inventory->cogs * $l_inventory->total_quantity;
                    $l_inventory->save();
                    $totalQty = (float) $l_inventory->total_quantity;
                } else {
                    $this->comment('if3');
                    $l_inventory->recalculate = 0;
                    // if value 0 from output
                    if ($l_inventory->price == 0) {
                        $l_inventory->price = $cogs;
                    }
                    $l_inventory->total_quantity = (float) $totalQty + (float) $l_inventory->quantity;
                    // $this->comment('calc = ' . $l_inventory->total_quantity . ' ' . (float) $totalQty . ' ' . (float) $l_inventory->quantity );
                    $l_inventory->total_value = $totalValue + ($l_inventory->quantity * $l_inventory->price);
                    if ((float) $l_inventory->cogs == 0) {
                        if ($l_inventory->item_id === 661) {
                            $this->comment($l_inventory->id . ' = ' . $l_inventory->cogs . ' = '. $cogs);
                        }
                        $l_inventory->cogs = $cogs;
                    } else {
                        $cogs = $l_inventory->cogs;
                    }
                    $l_inventory->total_value = $l_inventory->cogs * $l_inventory->total_quantity;
                    $l_inventory->save();
                    $totalQty = (float) $l_inventory->total_quantity;
                    $totalValue = $l_inventory->total_value;
                }

                // value = 0, if total qty = 0
                if ((float) $l_inventory->total_quantity <= 0) {
                    $this->comment('if4');
                    $l_inventory->total_value = 0;

                    if ((float) $l_inventory->total_quantity < 0) {
                        $l_inventory->recalculate = 1;
                    }

                    $l_inventory->save();
                }
            }
        }

        \DB::commit();
    }
}