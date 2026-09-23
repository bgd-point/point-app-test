<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockCorrection\StockCorrectionItem;
use Point\PointInventory\Helpers\StockCorrectionHelper;

class RecalculateZero extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * dev:recalculate:cutoff
     * dev:recalculate:zero
     * dev:recalculate:allval
     * dev:recalculate:all
     * dev:recalculate:jhppkb
     * dev:recalculate:mj
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:zero';

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

        \DB::beginTransaction();

        $inventories = Inventory::where('total_quantity_all', 0)->get();

        foreach ($inventories as $inventory) {
          $inventory->cogs = 0;
          $inventory->total_value = 0;
          $inventory->total_value_all = 0;
          $inventory->save();
        }

        \DB::commit();
    }
}
