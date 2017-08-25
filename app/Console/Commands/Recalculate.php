<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;

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

        $inventories = Inventory::where('recalculate', 1)
            ->groupBy('warehouse_id')
            ->groupBy('item_id')
            ->orderBy('form_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        \Log::info($inventories);
        \Log::info('==========================');

        foreach ($inventories as $inventory) {
            $list_inventory = Inventory::where('item_id', '=', $inventory->item_id)
                ->where('form_date', '>=', $inventory->form_date)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->orderBy('form_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            \Log::info($list_inventory);
            \Log::info('==========================');
            
            $total_quantity = 0;
            $total_value = 0;
            $cogs = 0;
            foreach ($list_inventory as $l_inventory) {
                $total_quantity += $l_inventory->total_quantity;
                $total_value += $l_inventory->quantity * $l_inventory->price;

                $l_inventory->total_quantity = $total_quantity;
                $l_inventory->total_value = $total_value;

                $l_inventory->cogs = 0;
                if ($l_inventory->total_quantity != 0) {
                    $l_inventory->cogs = $l_inventory->total_value / $l_inventory->total_quantity;
                }

                $l_inventory->recalculate = false;
                $l_inventory->save();

                \Log::info('total quantity : '. $l_inventory->total_quantity);
                \Log::info('total value : '. $l_inventory->total_value);
                \Log::info('cogs : '. $l_inventory->cogs);
            }
        }

        \DB::commit();
    }
}
