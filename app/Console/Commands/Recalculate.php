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

            foreach ($list_inventory as $l_inventory) {
                if ($l_inventory->quantity >= 0) {
                    $l_inventory->form_date = date('Y-m-d 00:00:00', strtotime($l_inventory->form_date));
                    $l_inventory->save();
                } else {
                    $l_inventory->form_date = date('Y-m-d 23:59:59', strtotime($l_inventory->form_date));
                    $l_inventory->save();
                }
            }

            $total_quantity = 0;
            $total_value = 0;
            $cogs = 0;
            $cogs_tmp = 0;
            foreach ($list_inventory as $l_inventory) {
                // UPDATE TOTAL QUANTITY
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

        \DB::commit();
    }
}
