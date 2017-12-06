<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Inventory;

class FixInventorySeeder extends Seeder
{
    public function run()
    {
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
                    if ($l_inventory->total_quantity > 0) {
                        $total_value += $l_inventory->quantity * $l_inventory->price;
                        $l_inventory->cogs = $l_inventory->total_value / $l_inventory->total_quantity;
                        if ($inventory->item_id == 21) {
                            \Log::info('cogs: ' . $l_inventory->formulir->form_number . ' = '. $l_inventory->total_value .' / '. $l_inventory->total_quantity . ' = '. $l_inventory->cogs);
                        }
                        $cogs_tmp = $l_inventory->cogs;
                    } else {
                        $l_inventory->recalculate = true;
                    }
                } else {
                    $l_inventory->cogs = $cogs_tmp;
                    if ($l_inventory->total_quantity < 0) {
                        $l_inventory->recalculate = true;
                        $total_value = 0;
                    } else {
                        $total_value += $l_inventory->quantity * $l_inventory->cogs;
                    }
                    if ($inventory->item_id == 21) {
                        \Log::info('cogs: ' . $l_inventory->total_value .' / '. $l_inventory->total_quantity . ' = '. $l_inventory->cogs);
                    }
                }
                $l_inventory->total_quantity = $total_quantity;
                $l_inventory->total_value = $l_inventory->total_quantity ? $total_value : 0;
                $l_inventory->save();
            }
        }

        \DB::commit();
    }
}
