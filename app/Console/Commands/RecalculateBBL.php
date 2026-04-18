<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Retur;

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

        

        $this->handleQty();
        // $this->handleValue();

        
    }

    public function handleQty()
    {
        $this->comment('handle inventory');

        // Get all items
        Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('inventory.quantity', 0)
            ->where('formulir.formulirable_type', '!=', 'Point\PointInventory\Models\StockOpname\StockOpname')
            ->delete();
        

        $inventories = Inventory::select('item_id', 'warehouse_id')
            ->groupBy('item_id', 'warehouse_id')
            ->get();

        foreach ($inventories as $inventory) {
            \DB::beginTransaction();
            if ($inventory->item_id === 877) {
                $this->comment($inventory);
            }
            
            $list_inventory = Inventory::with('formulir')->with('item')
                ->where('item_id', '=', $inventory->item_id)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            $prevCogs = 0;
            foreach($list_inventory as $index => $l_inventory) {
                if ($inventory->item_id === 877) {
                    $this->comment($l_inventory->formulir->form_number . ' = ' . $l_inventory->item->code . ' = ' . $l_inventory->form_date . ' = ' . $l_inventory->total_quantity);
                }
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
                    $l_inventory->save();
                    $prevCogs = $l_inventory->cogs;
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
                    $l_inventory->save();
                    $totalQty = (float) $l_inventory->total_quantity;
                    $prevCogs = $l_inventory->cogs;
                } else {
                    $l_inventory->recalculate = 0;
                    // if value 0 from output
                    if ($l_inventory->price == 0) {
                        $l_inventory->price = $cogs;
                    }
                    if ($l_inventory->quantity > 0 && $l_inventory->formulir->formulirable_type === StockCorrection::class) {
                        $l_inventory->price = $prevCogs;
                        $l_inventory->cogs = $prevCogs;
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
                    $l_inventory->save();
                    $totalQty = (float) $l_inventory->total_quantity;
                    $totalValue = $l_inventory->total_value;
                    $prevCogs = $l_inventory->cogs;
                }

                if ((float) $l_inventory->total_quantity <= 0) {
                    $l_inventory->total_value = 0;

                    if ((float) $l_inventory->total_quantity < 0) {
                        $l_inventory->recalculate = 1;
                    }

                    $l_inventory->save();
                }
            }
            \DB::commit();
        }
    }

    public function handleValue()
    {
        $this->comment('handle value');

        // Get all items
        Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('inventory.quantity', 0)
            ->where('formulir.formulirable_type', '!=', 'Point\PointInventory\Models\StockOpname\StockOpname')
            ->delete();
        
        $inventories = Inventory::orderBy('form_date', 'asc')
            ->where('item_id', 606)
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'];
            });

        $this->comment(count($inventories));

        foreach ($inventories as $inventory) {
            $list_inventory = Inventory::with('formulir')
                ->where('item_id', '=', $inventory->item_id)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            $prevCogs = 0;
            foreach($list_inventory as $index => $l_inventory) {
                $this->comment($l_inventory->id . ' = ' . $l_inventory->form_date . ' = ' . $prevCogs);
                if ($index == 0) {
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
                    $prevCogs = $l_inventory->cogs;
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
                    $l_inventory->save();
                    $totalQty = (float) $l_inventory->total_quantity;
                    $prevCogs = $l_inventory->cogs;
                } else {
                    $l_inventory->recalculate = 0;
                    // if value 0 from output
                    if ($l_inventory->price == 0) {
                        $l_inventory->price = $cogs;
                    }
                    if ($l_inventory->quantity > 0 && $l_inventory->formulir->formulirable_type === StockCorrection::class) {
                        $l_inventory->price = $prevCogs;
                        $l_inventory->cogs = $prevCogs;
                    }
                    if ($l_inventory->formulir->formulirable_type === TransferItem::class) {
                        $this->comment($l_inventory->formulir->form_number . 'transfer_item ' . $prevCogs);
                        $l_inventory->price = $prevCogs;
                        $l_inventory->cogs = $prevCogs;
                    }
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
                    $l_inventory->save();
                    $totalQty = (float) $l_inventory->total_quantity;
                    $totalValue = $l_inventory->total_value;
                    $prevCogs = $l_inventory->cogs;
                }

                if ((float) $l_inventory->total_quantity <= 0) {
                    $l_inventory->total_value = 0;

                    if ((float) $l_inventory->total_quantity < 0) {
                        $l_inventory->recalculate = 1;
                    }

                    $l_inventory->save();
                }
            }
        }
    }
}