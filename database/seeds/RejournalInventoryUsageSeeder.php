<?php

use Illuminate\Database\Seeder;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;
use Point\PointInventory\Models\InventoryUsage\InventoryUsageItem;

class RejournalInventoryUsageSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();
        
        
        $debit = 0;
        $credit = 0;

        $list_inventory_usage = InventoryUsage::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->selectOriginal()->get();
        
        foreach(list_inventory_usage as $inventory_usage) {
            foreach ($inventory_usage->listInventoryUsage as $inventory_usage_item) {
                $position = JournalHelper::position($inventory_usage_item->item->account_asset_id);

                $cost_of_sales = InventoryHelper::getCostOfSales($inventory_usage->formulir->created_at, $inventory_usage_item->item_id, $inventory_usage->warehouse_id);
                $cost_of_sales = $cost_of_sales * $inventory_usage_item->quantity_usage;

                $journal = new Journal();
                $journal->form_date = $inventory_usage->formulir->created_at;
                $journal->coa_id = $inventory_usage_item->item->account_asset_id;
                $journal->description = $inventory_usage_item->usage_notes;
                $journal->credit = abs($cost_of_sales);
                $journal->form_journal_id = $inventory_usage->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $inventory_usage_item->item_id;
                $journal->subledger_type = get_class($inventory_usage_item->item);
                $journal->save();

                // JOURNAL #1 of #2 - Inventory Usage Expense
                $inventory_usage_expense_account = JournalHelper::getAccount('point inventory usage', 'inventory differences');
                $position = JournalHelper::position($inventory_usage_expense_account);
                $journal = new Journal();
                $journal->form_date = $inventory_usage->formulir->created_at;
                $journal->coa_id = $inventory_usage_expense_account;
                $journal->description = $inventory_usage_item->usage_notes;
                $journal->debit = abs($cost_of_sales);
                $journal->form_journal_id = $inventory_usage->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }
        }

        \DB::commit();
    }
}
