<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;

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

//        $alsd = Allocation::find(1);
//        if ($alsd) {
//            $alsd->created_at = Carbon::now();
//            $alsd->save();
//        }
//
//        $opnames = StockOpname::join('formulir', 'formulir.id', '=', 'point_inventory_stock_opname.formulir_id')
//            ->where('formulir.form_date', '>=', '2019-10-01')
//            ->where('formulir.form_status', '>=', 1)
//            ->whereNotNull('formulir.form_number')
//            ->orderBy('formulir.form_date', 'asc')
//            ->select('point_inventory_stock_opname.*')
//            ->get();
//
//        foreach($opnames as $opname) {
//            foreach ($opname->items as $opnameItem) {
//                $inv = Inventory::where('inventory.item_id', $opnameItem->item_id)
//                    ->where('form_date', '<', $opname->formulir->form_date)
//                    ->where('warehouse_id', $opname->warehouse_id)
//                    ->orderBy('form_date', 'desc')
//                    ->first();
//
//                $stock = 0;
//
//                if ($inv) {
//                    $this->line('TOTAL QUANTITY ' . $inv->total_quantity);
//                    $stock = $inv->total_quantity;
//                }
//
//                $opnameItem->stock_in_database = $stock;
//                $opnameItem->save();
//            }
//
//            Inventory::where('formulir_id', $opname->formulir_id)->delete();
//
//            foreach ($opname->items as $opnameItem) {
//                $inventory = new Inventory;
//                $inventory->form_date = $opname->formulir->form_date;
//                $inventory->formulir_id = $opname->formulir_id;
//                $inventory->warehouse_id = $opname->warehouse_id;
//                $inventory->item_id = $opnameItem->item_id;
//                $inventory->price =  InventoryHelper::getCostOfSales($opname->formulir->form_date, $opnameItem->item_id, $opname->warehouse_id);
//                $quantity = $opnameItem->quantity_opname - $opnameItem->stock_in_database;
//                if ($quantity < 0) {
//                    $inventory->quantity = $quantity * -1;
//                    $inventory_helper = new InventoryHelper($inventory);
//                    $inventory_helper->out();
//                } elseif ($quantity > 0) {
//                    $inventory->quantity = $quantity;
//                    $inventory_helper = new InventoryHelper($inventory);
//                    $inventory_helper->in();
//                } else {
//                    continue;
//                }
//            }
//        }

        \DB::commit();

        \DB::beginTransaction();

        $inventories = Inventory::orderBy('form_date', 'asc')
            ->orderBy('formulir_id', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'].$inventory['warehouse_id'];
            });

        foreach ($inventories as $inventory) {
            $count = 0;
            $list_inventory = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->where('form_date', '>=', '2019-10-01')
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($list_inventory as $l_inventory) {
                $l_inventory->recalculate = false;
                if ($l_inventory->formulir->formulirable_type === StockOpname::class) {
                    $st = StockOpname::where('formulir_id', '=', $l_inventory->formulir->id)->first();
                    $l_inventory->form_date = date('Y-m-d H:i:s', strtotime($st->formulir->form_date));
                    $l_inventory->save();
                } else if ($l_inventory->quantity >= 0) {
                    $l_inventory->form_date = date('Y-m-d 00:00:00', strtotime($l_inventory->form_date));
                    $l_inventory->save();
                } else {
                    $l_inventory->form_date = date('Y-m-d 23:59:00', strtotime($l_inventory->form_date));
                    $l_inventory->save();
                }
            }

            $total_quantity = 0;
            $total_value = 0;
            $cogs = 0;
            $cogs_tmp = 0;
            foreach ($list_inventory as $index => $l_inventory) {
                // UPDATE QUANTITY IF FORMULIR TYPE IS STOCKOPNAME
                if ($l_inventory->formulir->formulirable_type === StockOpname::class && $index > 0) {
                    $stockopname = StockOpname::join('point_inventory_stock_opname_item', 'point_inventory_stock_opname.id', '=', 'point_inventory_stock_opname_item.stock_opname_id')
                    ->where('point_inventory_stock_opname.formulir_id', $l_inventory->formulir_id)
                    ->where('point_inventory_stock_opname_item.item_id', $l_inventory->item_id)
                    ->select('point_inventory_stock_opname_item.quantity_opname')
                    ->first();

                    $st = Inventory::where('item_id', $l_inventory->item_id)
                        ->where('warehouse_id', $l_inventory->warehouse_id)
                        ->where('form_date', '<', $l_inventory->form_date)
                        ->orderBy('form_date', 'desc')
                        ->orderBy('formulir_id', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    foreach($stockopname->items as $sItem) {
                        if ($sItem == $l_inventory->item_id) {
                            $sItem->stock_in_database = $st->total_quantity;
                            $sItem->save();
                            break;
                        }
                    }

                    $total_quantity = $stockopname->quantity_opname;
                    $l_inventory->total_quantity = $total_quantity;
                    $l_inventory->quantity = $total_quantity - $list_inventory[$index-1]->total_quantity;
                    $l_inventory->save();
                } else {
                    // UPDATE TOTAL QUANTITY IF FORMULIR TYPE IS NOT STOCKOPNAME
                    $total_quantity += $l_inventory->quantity;
                    if ($l_inventory->quantity > 0) {
                        // STOCK IN
                        if ($total_quantity <= 0) {
                            // IGNORE VALUE BECAUSE USER ERROR (STOCK MINUS)
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
        }

        \DB::commit();
    }
}
