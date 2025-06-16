<?php

namespace Point\PointInventory\Helpers;

use Point\Core\Models\Vesa;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Inventory;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;

class StockOpnameHelper
{
    public static function searchList($list_stock_opname, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_stock_opname = $list_stock_opname->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $list_stock_opname = $list_stock_opname->orderBy($order_by, $order_type);
        } else {
            $list_stock_opname = $list_stock_opname->orderByStandard();
        }

        
        if ($date_from) {
            $list_stock_opname = $list_stock_opname->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_stock_opname = $list_stock_opname->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            $list_stock_opname = $list_stock_opname->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                  ->orWhere('warehouse.name', 'like', '%'.$search.'%');
            });
        }

        return $list_stock_opname;
    }

    public static function create($formulir)
    {
        $stock_opname = new StockOpname;
        $stock_opname->formulir_id = $formulir->id;
        $stock_opname->warehouse_id = app('request')->input('warehouse_id');
        $stock_opname->save();

        for ($i=0 ; $i<count(app('request')->input('item_id')) ; $i++) {
            if ($i < 166) {
                $stock_opname_items = new StockOpnameItem;
                $stock_opname_items->stock_opname_id = $stock_opname->id;
                $stock_opname_items->item_id = app('request')->input('item_id')[$i];
                $stock_opname_items->stock_in_database = number_format_db(app('request')->input('stock_in_database')[$i]);
                $stock_opname_items->quantity_opname = number_format_db(app('request')->input('quantity_opname')[$i]);
                $stock_opname_items->opname_notes = app('request')->input('opname_notes')[$i];
                $unit = $stock_opname_items->item->unit()->first();
                $stock_opname_items->unit = $unit->name;
                $stock_opname_items->converter = $unit->converter;
                $stock_opname_items->save();
            }
        }

        return $stock_opname;
    }

    public static function approve($stock_opname)
    {
        foreach ($stock_opname->items as $stock_opname_item) {
            $inventory = new Inventory;
            $inventory->form_date = date('Y-m-d H:i:s');
            $inventory->formulir_id = $stock_opname->formulir_id;
            $inventory->warehouse_id = $stock_opname->warehouse_id;
            $inventory->item_id = $stock_opname_item->item_id;
            $inventory->price =  InventoryHelper::getCostOfSales(date('Y-m-d H:i:s'), $stock_opname_item->item_id, $stock_opname->warehouse_id);
            $quantity = $stock_opname_item->quantity_opname - $stock_opname_item->stock_in_database;
            if ($quantity < 0) {
                $inventory->quantity = $quantity * -1;
                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->out();
            } elseif ($quantity > 0) {
                $inventory->quantity = $quantity;
                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();
            } else {
                continue;
            }
        }
    }

    public static function updateJournal($stock_opname)
    {
        $debit = 0;
        $credit = 0;

        // JOURNAL #2 of #2 - Invetory
        foreach ($stock_opname->items as $stock_opname_item) {
            $position = JournalHelper::position($stock_opname_item->item->account_asset_id);

            $cost_of_sales = InventoryHelper::getCostOfSales(date('Y-m-d H:i:s'), $stock_opname_item->item_id, $stock_opname->warehouse_id);
            $quantity = $stock_opname_item->quantity_opname - $stock_opname_item->stock_in_database;

            if ($quantity == 0) {
                continue;
            } else {
                $cost_of_sales = $cost_of_sales * $quantity;
            }

            $journal = new Journal();
            $journal->form_date = date('Y-m-d H:i:s');
            $journal->coa_id = $stock_opname_item->item->account_asset_id;
            $journal->description = $stock_opname_item->opname_notes;
            $journal->$position = $cost_of_sales;
            $journal->form_journal_id = $stock_opname->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $stock_opname_item->item_id;
            $journal->subledger_type = get_class($stock_opname_item->item);
            $journal->save();

            if ($position == 'debit') {
                $debit += $stock_opname_item->amount;
            } else {
                $credit += $stock_opname_item->amount;
            }

            // JOURNAL #1 of #2 - Inventory Differences
            $inventory_differences_account = JournalHelper::getAccount('point inventory stock opname', 'inventory differences');
            $position = JournalHelper::position($inventory_differences_account);
            $journal = new Journal();
            $journal->form_date = date('Y-m-d H:i:s');
            $journal->coa_id = $inventory_differences_account;
            $journal->description = $stock_opname_item->opname_notes;
            $journal->$position = $cost_of_sales * -1;
            $journal->form_journal_id = $stock_opname->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            if ($position == 'debit') {
                $debit += $stock_opname->total;
            } else {
                $credit += $stock_opname->total;
            }

            if ($debit != $credit) {
                throw new PointException('Unbalance Journal');
            }
        }
    }
}
