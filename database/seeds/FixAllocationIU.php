<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\AllocationHelper;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;

class FixAllocationIU extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        foreach (InventoryUsage::all() as $inventory_usage) {
            foreach ($inventory_usage->listInventoryUsage as $inventory_usage_item) {
                $inventory_quantity = $inventory_usage_item->quantity_usage;
                $inventory_price =  InventoryHelper::getCostOfSales($inventory_usage->formulir->form_date, $inventory_usage_item->item_id, $inventory_usage->warehouse_id);

                AllocationHelper::save($inventory_usage->formulir->id, $inventory_usage_item->allocation_id, $inventory_quantity * $inventory_price, $inventory_usage_item->usage_notes);
            }
        }

        \DB::commit();
    }
}
