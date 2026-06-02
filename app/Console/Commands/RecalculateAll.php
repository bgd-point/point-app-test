<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Retur;
use Point\Framework\Models\Master\Warehouse;

class RecalculateAllVal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:all';

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

        // $items = Item::where('id',102)->get();
        $items = Item::all();
        
        $i = 0;
        foreach ($items as $item) {
            \DB::beginTransaction();
            $i++;
            
            $list_inventory = Inventory::where('item_id', '=', $item->id)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            $this->comment('I' . count($items) . ' = ' . $i);
            foreach($list_inventory as $index => $l_inventory) {
                $l_inventory->total_value = $l_inventory->total_quantity * $l_inventory->cogs;
                $l_inventory->save();
            }
            \DB::commit();
        }
        
    }
}