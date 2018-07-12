<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;

class Recalculate2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate2';

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

        $inventories = Inventory::orderBy('form_date', 'asc')
            ->orderBy('formulir_id', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'].$inventory['warehouse_id'];
            });

        foreach ($inventories as $inventory) {
            $count = 0;
            $list_inventory = Inventory::where('item_id', '=', $inventory->item_id)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->where('form_date', '>=', $inventory->form_date)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $total_quantity = 0;
            $previous_cogs = 0;
            $previous_total_quantity = 0;
            foreach ($list_inventory as $l_inventory) {

                $total_quantity += $l_inventory->quantity;
                
                if ($l_inventory->quantity > 0) { // stock in
                    if($previous_total_quantity + $l_inventory->quantity != 0) {
                        $l_inventory->cogs = ($previous_cogs * $previous_total_quantity + $l_inventory->quantity * $l_inventory->price) / ($previous_total_quantity + $l_inventory->quantity);
                    }
                    else {
                        $l_inventory->cogs = $previous_cogs;
                    }
                }
                else { // stock out
                    $l_inventory->cogs = $previous_cogs;
                }
                $previous_cogs = $l_inventory->cogs;
                $previous_total_quantity = $total_quantity;
                $l_inventory->total_quantity = $total_quantity;
                $l_inventory->total_value = $total_quantity * $l_inventory->cogs;

                $l_inventory->recalculate = false;
                
                if($total_quantity < 0)
                    $l_inventory->recalculate = true;

                $l_inventory->save();
            }
        }

        \DB::commit();
    }
}
