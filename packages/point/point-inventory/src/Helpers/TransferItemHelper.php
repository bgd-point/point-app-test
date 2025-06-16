<?php

namespace Point\PointInventory\Helpers;

use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Inventory;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Master\Item;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointInventory\Models\TransferItem\TransferItemDetail;

class TransferItemHelper
{
    public static function searchList($list_tranfers_item, $order_by, $order_type, $status, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_tranfers_item = $list_tranfers_item->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $list_tranfers_item = $list_tranfers_item->orderBy($order_by, $order_type);
        } else {
            $list_tranfers_item = $list_tranfers_item->orderByStandard();
        }

        if ($date_from) {
            $list_tranfers_item = $list_tranfers_item->where('formulir.form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_tranfers_item = $list_tranfers_item->where('formulir.form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            $list_tranfers_item = $list_tranfers_item->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_tranfers_item;
    }

    public static function create($formulir)
    {
        $transfer_item = new TransferItem;
        $transfer_item->formulir_id = $formulir->id;
        $transfer_item->warehouse_sender_id = app('request')->input('warehouse_id');
        $transfer_item->warehouse_receiver_id = app('request')->input('warehouse_to');
        $transfer_item->save();

        for ($i=0 ; $i<count(app('request')->input('item_id')) ; $i++) {
            $transfer_item_detail = new TransferItemDetail;
            $transfer_item_detail->transfer_item_id = $transfer_item->id;
            $transfer_item_detail->item_id = app('request')->input('item_id')[$i];
            $transfer_item_detail->qty_send = number_format_db(app('request')->input('qty_send')[$i]);
            $transfer_item_detail->qty_received = 0;
            $transfer_item_detail->unit = Item::defaultUnit($transfer_item_detail->item_id)->name;
            $transfer_item_detail->converter = 1;
            $transfer_item_detail->cogs = InventoryHelper::getCostOfSales($formulir->created_at, $transfer_item_detail->item_id, $transfer_item->warehouse_sender_id);
            $transfer_item_detail->save();
        }

        return $transfer_item;
    }

    public static function approve($transfer_item)
    {
        self::updateInventory($transfer_item);
        self::updateJournal($transfer_item);
    }

    private static function updateInventory($transfer_item)
    {
        foreach ($transfer_item->items as $transfer_item_detail) {
            $inventory = new Inventory;
            $inventory->form_date = date('Y-m-d H:i:s');
            $inventory->formulir_id = $transfer_item->formulir_id;
            $inventory->warehouse_id = $transfer_item->warehouse_sender_id;
            $inventory->item_id = $transfer_item_detail->item_id;
            $inventory->quantity = $transfer_item_detail->qty_send;
            $inventory->price = $transfer_item_detail->cogs;
            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->out();
        }
    }

    private static function updateJournal($transfer_item)
    {
        $debit = 0;
        $credit = 0;


        foreach ($transfer_item->items as $transfer_item_detail) {

            // JOURNAL #1 of #2 - SEND ITEM
            $position = JournalHelper::position($transfer_item_detail->item->account_asset_id);
            $journal = new Journal();
            $journal->form_date = date('Y-m-d H:i:s');
            $journal->coa_id = $transfer_item_detail->item->account_asset_id;
            $journal->description = $transfer_item->formulir->form_number;
            $journal->$position = $transfer_item_detail->cogs * $transfer_item_detail->qty_send * -1;
            $journal->form_journal_id = $transfer_item->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $transfer_item_detail->item_id;
            $journal->subledger_type = get_class($transfer_item_detail->item);
            $journal->save();

            if ($position == 'debit') {
                $debit += $transfer_item_detail->amount;
            } else {
                $credit += $transfer_item_detail->amount;
            }

            // JOURNAL #2 of #2 - ITEM IN TRANSIT
            $inventory_in_transit = JournalHelper::getAccount('point inventory transfer item', 'inventory in transit');
            $position = JournalHelper::position($inventory_in_transit);
            $journal = new Journal();
            $journal->form_date = date('Y-m-d H:i:s');
            $journal->coa_id = $inventory_in_transit;
            $journal->description = $transfer_item->formulir->form_number;
            $journal->$position = $transfer_item_detail->cogs * $transfer_item_detail->qty_send;
            $journal->form_journal_id = $transfer_item->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $transfer_item_detail->item_id;
            $journal->subledger_type = get_class($transfer_item_detail->item);
            $journal->save();

            if ($position == 'debit') {
                $debit += $transfer_item->total;
            } else {
                $credit += $transfer_item->total;
            }

            if ($debit != $credit) {
                throw new PointException('Unbalance Journal');
            }
        }
    }
}
