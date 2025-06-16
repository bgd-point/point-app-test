<?php

namespace Point\PointInventory\Helpers;

use Point\Core\Models\Vesa;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Inventory;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockCorrection\StockCorrectionItem;

class StockCorrectionHelper
{
    public static function searchList($list_stock_correction, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_stock_correction = $list_stock_correction->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $list_stock_correction = $list_stock_correction->orderBy($order_by, $order_type);
        } else {
            $list_stock_correction = $list_stock_correction->orderByStandard();
        }

        if ($date_from) {
            $list_stock_correction = $list_stock_correction->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_stock_correction = $list_stock_correction->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_stock_correction = $list_stock_correction->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                  ->orWhere('warehouse.name', 'like', '%'.$search.'%');
            });
        }

        return $list_stock_correction;
    }

    public static function create($formulir)
    {
        $stock_correction = new StockCorrection;
        $stock_correction->formulir_id = $formulir->id;
        $stock_correction->warehouse_id = app('request')->input('warehouse_id');
        $stock_correction->save();

        for ($i=0 ; $i<count(app('request')->input('item_id')) ; $i++) {
            $stock_correction_item = new StockCorrectionItem;
            $stock_correction_item->point_inventory_stock_correction_id = $stock_correction->id;
            $stock_correction_item->item_id = app('request')->input('item_id')[$i];
            $stock_correction_item->stock_in_database = number_format_db(app('request')->input('stock_exist')[$i]);
            $stock_correction_item->quantity_correction = number_format_db(app('request')->input('quantity_correction')[$i]);
            $stock_correction_item->correction_notes = app('request')->input('correction_notes')[$i];
            $unit = $stock_correction_item->item->unit()->first();
            $stock_correction_item->unit = $unit->name;
            $stock_correction_item->converter = $unit->converter;
            $stock_correction_item->save();
        }

        return $stock_correction;
    }

    public static function approve($stock_correction)
    {
        foreach ($stock_correction->items as $stock_correction_item) {
            $inventory = new Inventory;
            $inventory->form_date = date('Y-m-d H:i:s');
            $inventory->formulir_id = $stock_correction->formulir_id;
            $inventory->warehouse_id = $stock_correction->warehouse_id;
            $inventory->item_id = $stock_correction_item->item_id;
            $inventory->quantity = $stock_correction_item->quantity_correction;
            $inventory->price =  InventoryHelper::getCostOfSales(date('Y-m-d H:i:s'), $stock_correction_item->item_id, $stock_correction->warehouse_id);

            if ($inventory->quantity < 0) {
                $inventory->quantity *= -1;
                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->out();
            } else {
                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();
            }
        }
    }

    public static function updateJournal($stock_correction)
    {
        $debit = 0;
        $credit = 0;

        // JOURNAL #1 of #2 - Invetory
        foreach ($stock_correction->items as $stock_correction_item) {
            $position = JournalHelper::position($stock_correction_item->item->account_asset_id);

            $cost_of_sales = InventoryHelper::getCostOfSales(date('Y-m-d H:i:s'), $stock_correction_item->item_id, $stock_correction->warehouse_id);
            $cost_of_sales = $cost_of_sales * $stock_correction_item->quantity_correction;

            $journal = new Journal();
            $journal->form_date = date('Y-m-d H:i:s');
            $journal->coa_id = $stock_correction_item->item->account_asset_id;
            $journal->description = $stock_correction_item->correction_notes;
            $journal->$position = $cost_of_sales;
            $journal->form_journal_id = $stock_correction->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $stock_correction_item->item_id;
            $journal->subledger_type = get_class($stock_correction_item->item);
            $journal->save();

            if ($position == 'debit') {
                $debit += $stock_correction_item->amount;
            } else {
                $credit += $stock_correction_item->amount;
            }

            // JOURNAL #2 of #2 - Inventory Differences
            $inventory_differences_account = JournalHelper::getAccount('point inventory stock correction', 'inventory differences');

            $position = JournalHelper::position($inventory_differences_account);
            $journal = new Journal();
            $journal->form_date = date('Y-m-d H:i:s');
            $journal->coa_id = $inventory_differences_account;
            $journal->description = $stock_correction_item->correction_notes;
            $journal->$position = $cost_of_sales * -1;
            $journal->form_journal_id = $stock_correction->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            if ($position == 'debit') {
                $debit += $stock_correction_item->total;
            } else {
                $credit += $stock_correction_item->total;
            }

            if ($debit != $credit) {
                throw new PointException('Unbalance Journal');
            }
        }
    }
}
