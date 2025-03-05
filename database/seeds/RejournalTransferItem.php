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
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointInventory\Models\TransferItem\TransferItemDetail;

class RejournalTransferItemSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        \Log::info('---- Seeder Transfer Item starting ----');
        $this->transferItem();
        \Log::info('---- Seeder Transfer Item finished ----');

        \DB::commit();
    }

    public function transferItem()
    {
        $list_transfer_item = TransferItem::joinFormulir()->where('formulir.form_date', '>=', '2025-02-01')->notArchived()->approvalApproved()->selectOriginal()->get();
        \Log::info('---- Transfer Item starting ----');
        \Log::info($list_transfer_item);
        foreach($list_transfer_item as $transfer_item) {
            \Log::info($transfer_item->id);
            \Log::info($transfer_item->formulir_id);
            Journal::where('form_journal_id', $transfer_item->formulir_id)->delete();
            \Log::info('---- Sent Update ----');
            self::updateJournal($transfer_item);
            \Log::info('---- Receive Update ----');
            if ($transfer_item->received_date) {
                foreach ($transfer_item->items as $transfer_item_detail) {
                    // JOURNAL #1 of #2 - Invetory Received
                    $journal = new Journal();
                    $journal->form_date = $transfer_item->received_date;
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
                    $journal->form_date = $transfer_item->received_date;
                    $journal->coa_id = $in_transit_account;
                    $journal->description = 'receive item ' . $transfer_item->formulir->form_number;
                    $journal->debit = 0;
                    $journal->credit = $transfer_item_detail->cogs * $transfer_item_detail->qty_received;
                    $journal->form_journal_id = $transfer_item->formulir_id;
                    $journal->form_reference_id;
                    $journal->subledger_id = $transfer_item_detail->item_id;
                    $journal->subledger_type = get_class($transfer_item_detail->item);
                    $journal->save();
                }
            }
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
            $journal->form_date = $transfer_item->formulir->form_date;
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
            $journal->form_date = $transfer_item->formulir->form_date;
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