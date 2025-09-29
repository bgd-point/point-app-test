<?php

namespace Point\PointInventory\Helpers;

use Point\Framework\Helpers\JournalHelper;
use Point\Core\Helpers\DateHelper;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Inventory;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\PointInventory\Models\TransferItem\TransferItemDetail;

class ReceiveItemHelper
{
    public static function create($receive_item)
    {
        $date = app('request')->input('form_date');
        $form_date = DateHelper::formatDB($date, app('request')->input('time'));

        $item = app('request')->input('item_id');
        $quantity = app('request')->input('quantity_transfer');
        $price = app('request')->input('cogs');

        $receive_item->received_date = $form_date;
        $receive_item->save();

        for ($i=0; $i < count($item); $i++) {
            $transfer_item_detail = TransferItemDetail::where('transfer_item_id', $receive_item->id)->where('item_id', $item[$i])->first();
            $transfer_item_detail->qty_received = number_format_db($quantity[$i]);
            $transfer_item_detail->save();

            $inventory = new Inventory;
            $inventory->form_date = date('Y-m-d H:i:s');
            $inventory->formulir_id = $receive_item->formulir_id;
            $inventory->warehouse_id = $receive_item->warehouse_receiver_id;
            $inventory->item_id = $transfer_item_detail->item_id;
            $inventory->price = number_format_db($price[$i]);
            $inventory->quantity = number_format_db($quantity[$i]);
            if ($quantity > 0) {
                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();
            }
        }

        FormulirHelper::close($receive_item->formulir_id);
    }

    public static function updateJournal($transfer_item)
    {
        foreach ($transfer_item->items as $transfer_item_detail) {
            // JOURNAL #1 of #2 - Invetory Received
            $journal = new Journal();
            $journal->form_date = $transfer_item->formulir->form_date;
            $journal->coa_id = $transfer_item_detail->item->account_asset_id;
            $journal->description = 'receive item ' . $transfer_item->formulir->form_number;
            $journal->debit = $transfer_item_detail->cogs * $transfer_item_detail->qty_received;
            $journal->credit = 0;
            $journal->form_journal_id = $transfer_item->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $transfer_item_detail->item_id;
            $journal->subledger_type = get_class($transfer_item_detail->item);
            $journal->save();

            // JOURNAL #2 of #2 - Inventory In Transit
            $in_transit_account = JournalHelper::getAccount('point inventory transfer item', 'inventory in transit');
            $journal = new Journal();
            $journal->form_date = $transfer_item->formulir->form_date;
            $journal->coa_id = $in_transit_account;
            $journal->description = 'receive item ' . $transfer_item->formulir->form_number;
            $journal->debit = 0;
            $journal->credit = $transfer_item_detail->cogs * $transfer_item_detail->qty_received;
            $journal->form_journal_id = $transfer_item->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }
    }
}
