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

class Recalculate6 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:6';

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

        foreach ($items as $item) {
            if ($item) {
                $inventory = Inventory::where('item_id', '=', $item->id)
                    ->where('form_date', '<', '2026-05-05')
                    ->orderBy('form_date', 'desc')
                    ->orderBy('formulir_id', 'desc')
                    ->first();
                if (!$inventory) {
                    continue;
                }

                 $list_inventory = Inventory::where('item_id', '=', $item->id)
                    ->where('form_date', '>=', $inventory->form_date)
                    ->orderBy('form_date', 'asc')
                    ->orderBy('formulir_id', 'asc')
                    ->get();
                $list_inventory = Inventory::where('item_id', '=', $item->id)
                    ->where('form_date', '>=', $inventory->form_date)
                    ->orderBy('form_date', 'asc')
                    ->orderBy('formulir_id', 'asc')
                    ->get();

                $prevTotalQty = 0;
                $prevTotalVal = 0;
                $i=0;
                foreach($list_inventory as $index => $l_inventory) {
                    if ($i == 0) {
                        $i++;
                        $prevTotalQty = $l_inventory->total_quantity_all;
                        $prevTotalVal = $l_inventory->total_value_all;
                        continue;
                    }

                    if ($l_inventory->quantity < 0) {
                        if ($prevTotalQty == 0) {
                            $l_inventory->price = 0;
                        } else {
                            $l_inventory->price = $prevTotalVal / $prevTotalQty;
                        }
                    }

                    if ($l_inventory->quantity > 0) {
                        $this->comment($l_inventory->formulir->formulirable_type);
                        if ($l_inventory->formulir->formulirable_type === 'Point\PointInventory\Models\StockOpname\StockOpname' 
                            || $l_inventory->formulir->formulirable_type === 'Point\PointInventory\Models\StockCorrection\StockCorrection') {
                            // $this->comment('Stock Correction / Stock Opname');
                            if ($prevTotalQty == 0) {
                                $is = Inventory::where('item_id', '=', $item->id)
                                    ->where('price', '>', 0)
                                    ->orderBy('form_date', 'desc')
                                    ->first();

                                if ($item->id === 608) {
                                    $this->comment('Found inventory with price > 0 : ' . $is->id . ' => ' . $is->price);
                                }
                                if ($is) {
                                    $l_inventory->price = $is->cogs;
                                } else {    
                                    $l_inventory->price = 0;
                                }
                            } else {
                                $l_inventory->price = $prevTotalVal / $prevTotalQty;
                            }
                        }
                    }
                    
                    // $l_inventory->total_value_all = $prevTotalVal + ($l_inventory->quantity * $l_inventory->price);
                    if (!$l_inventory->total_quantity_all || $l_inventory->total_quantity_all == 0) {
                        // $l_inventory->cogs = 0;
                    } else {
                        // $l_inventory->cogs = $l_inventory->total_value_all / $l_inventory->total_quantity_all;
                    }
                    $l_inventory->save();

                    // $prevTotalVal += ($l_inventory->quantity * $l_inventory->price);
                    // $prevTotalQty += $l_inventory->quantity;
                }
            }
        }
    }
}