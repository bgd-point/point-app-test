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

class RecalculateQty extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * dev:recalculate:cutoff
     * dev:recalculate:allval
     * dev:recalculate:all
     * dev:recalculate:jhppkb
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:qty';

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

        $data = json_decode($json, true);

        \DB::beginTransaction();

        $inventories = Inventory::where('item_id', 904)
          ->orderBy('form_date', 'asc')
          ->orderBy('formulir_id', 'asc')
          ->orderBy('id', 'asc')
          ->get();

        $total = 0;

        foreach ($inventories as $inventory) {
          $total += $inventory->quantity;

          $inventory->quantity_all = $total;
          $inventory->save();
        }

        \DB::commit();
    }
}
