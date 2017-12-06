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
            $total_value = 0;
            $cogs_tmp = 0;
            foreach ($list_inventory as $l_inventory) {

                $total_quantity += $l_inventory->quantity;
                if ($l_inventory->quantity > 0) {
                    $total_value += $l_inventory->quantity * $l_inventory->price;
                } else {
                    $total_value += $l_inventory->quantity * $l_inventory->cogs;
                }

                $l_inventory->total_quantity = $total_quantity;
                $l_inventory->total_value = $l_inventory->total_quantity ? $total_value : 0;

                if ($l_inventory->quantity > 0) {
                    if ($l_inventory->total_quantity > 0) {
                        $l_inventory->cogs = $l_inventory->total_value / $l_inventory->total_quantity;
                        $cogs_tmp = $l_inventory->cogs;
                    }
                } else {
                    $l_inventory->cogs = $cogs_tmp;
                }

                $l_inventory->recalculate = false;
                $l_inventory->save();
            }
        }

        \DB::commit();
    }
}
