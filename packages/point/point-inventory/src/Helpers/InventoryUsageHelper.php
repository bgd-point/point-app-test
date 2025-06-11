<?php

namespace Point\PointInventory\Helpers;

use Point\Core\Models\Vesa;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;
use Point\PointInventory\Models\InventoryUsage\InventoryUsageItem;

class InventoryUsageHelper
{
    public static function searchList($list_inventory_usage, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_inventory_usage = $list_inventory_usage->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $list_inventory_usage = $list_inventory_usage->orderBy($order_by, $order_type);
        } else {
            $list_inventory_usage = $list_inventory_usage->orderByStandard();
        }

        if ($date_from) {
            $list_inventory_usage = $list_inventory_usage->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_inventory_usage = $list_inventory_usage->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            $list_inventory_usage = $list_inventory_usage->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                  ->orWhere('warehouse.name', 'like', '%'.$search.'%');
            });
        }

        return $list_inventory_usage;
    }

    public static function create($formulir)
    {
        $inventory_usage = new InventoryUsage;
        $inventory_usage->formulir_id = $formulir->id;
        $inventory_usage->warehouse_id = app('request')->input('warehouse_id');
        $inventory_usage->employee_id = app('request')->input('employee_id');
        $inventory_usage->save();

        for ($i=0 ; $i<count(app('request')->input('item_id')) ; $i++) {
            $inventory_usage_items = new InventoryUsageItem;
            $inventory_usage_items->inventory_usage_id = $inventory_usage->id;
            $inventory_usage_items->item_id = app('request')->input('item_id')[$i];
            $inventory_usage_items->stock_in_database = number_format_db(app('request')->input('stock_exist')[$i]);
            $inventory_usage_items->quantity_usage = number_format_db(app('request')->input('quantity_usage')[$i]);
            $inventory_usage_items->usage_notes = app('request')->input('usage_notes')[$i];
            $inventory_usage_items->allocation_id = app('request')->input('allocation_id')[$i];
            $inventory_usage_items->unit = Item::defaultUnit($inventory_usage_items->item_id)->name;
            $inventory_usage_items->converter = '1';
            $inventory_usage_items->save();
        }

        return $inventory_usage;
    }

    public static function approve($inventory_usage)
    {
        foreach ($inventory_usage->listInventoryUsage as $inventory_usage_item) {
            $inventory = new Inventory;
            $inventory->form_date = $inventory_usage->formulir->created_at;
            $inventory->formulir_id = $inventory_usage->formulir_id;
            $inventory->warehouse_id = $inventory_usage->warehouse_id;
            $inventory->item_id = $inventory_usage_item->item_id;
            $inventory->quantity = $inventory_usage_item->quantity_usage;
            $inventory->price =  InventoryHelper::getCostOfSales($inventory_usage->formulir->created_at, $inventory_usage_item->item_id, $inventory_usage->warehouse_id);

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->out();

            AllocationHelper::save($inventory_usage->formulir->id, $inventory_usage_item->allocation_id, $inventory->quantity * $inventory->price, $inventory_usage_item->usage_notes);
        }
    }

    public static function updateJournal($inventory_usage)
    {
        $debit = 0;
        $credit = 0;

        // JOURNAL #2 of #2 - Invetory
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

            // if ($position == 'debit') {
            //     $debit += $inventory_usage_item->amount;
            // } else {
            //     $credit += $inventory_usage_item->amount;
            // }

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

            // if ($position == 'debit') {
            //     $debit += $inventory_usage->total;
            // } else {
            //     $credit += $inventory_usage->total;
            // }

            // if ($debit != $credit) {
            //     throw new PointException('Unbalance Journal');
            // }
        }
    }
}
