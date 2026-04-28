<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\TransferItem\TransferItem;

class Reti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:reti';

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

        $formulirs = Formulir::where('formulirable_type', '=', TransferItem::class)
            ->where(function ($q) {
                $q->where('approval_status', -1)
                    ->orWhereNull('form_number')
                    ->orWhere('form_status', -1);
            })
            // ->where('form_date', '>=', '2026-04-01 00:00:00')
            ->get();

        foreach ($formulirs as $formulir) {
            $this->line('FORM NUMBER : ' . $formulir->form_number . ' DELETED');
            Inventory::where('formulir_id', $formulir->id)->delete();
            Journal::where('form_journal_id', $formulir->id)->delete();
        }

        $formulirs = Formulir::where('formulirable_type', '=', TransferItem::class)
            ->whereNotNull('form_number')
            ->whereNull('canceled_at')
            ->where('approval_status', '=', 11)
            // ->where('form_date', '>=', '2026-04-01 00:00:00')
            ->get();

        foreach ($formulirs as $formulir) {
            $qty = 0;

            $this->line('FORM NUMBER : ' . $formulir->form_number . ' ADDED');

            $transfer_item = TransferItem::where('formulir_id', $formulir->id)->first();

            Inventory::where('formulir_id', $transfer_item->formulir->id)->delete();
            Journal::where('form_journal_id', $transfer_item->formulir->id)->delete();

            foreach ($transfer_item->items as $transfer_item_detail) {
                $inventory = new Inventory;
                $inventory->form_date = $transfer_item->formulir->form_date;
                $inventory->formulir_id = $transfer_item->formulir_id;
                $inventory->warehouse_id = $transfer_item->warehouse_sender_id;
                $inventory->item_id = $transfer_item_detail->item_id;
                $inventory->quantity = $transfer_item_detail->qty_send;
                $inventory->price = $transfer_item_detail->cogs;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->out();

                $inventory = new Inventory;
                $inventory->form_date = $transfer_item->formulir->form_date;
                $inventory->formulir_id = $transfer_item->formulir_id;
                $inventory->warehouse_id = $transfer_item->warehouse_receiver_id;
                $inventory->item_id = $transfer_item_detail->item_id;
                $inventory->price = $transfer_item_detail->cogs;
                $inventory->quantity = $transfer_item_detail->qty_received;
                if ($transfer_item_detail->qty_received > 0) {
                    $inventory_helper = new InventoryHelper($inventory);
                    $inventory_helper->in();
                }
            }

            self::updateJournalTI($transfer_item);
            self::updateJournalRI($transfer_item);
        }

        \DB::commit();
    }

    public static function updateJournalTI($transfer_item)
    {
        $debit = 0;
        $credit = 0;
        foreach ($transfer_item->items as $transfer_item_detail) {

            // JOURNAL #1 of #2 - SEND ITEM
            $position = JournalHelper::position($transfer_item_detail->item->account_asset_id);
            $journal = new Journal();
            $journal->form_date = $transfer_item->formulir->approval_at;
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
            $journal->form_date = $transfer_item->formulir->approval_at;
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

    public static function updateJournalRI($transfer_item)
    {
        foreach ($transfer_item->items as $transfer_item_detail) {
            if ($transfer_item_detail->qty_received != 0) {
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
