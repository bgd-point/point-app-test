<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
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
            })->where('form_date', '>=', '2025-05-01 00:00:00')->get();

        foreach ($formulirs as $formulir) {
            $qty = 0;
            $inventories = Inventory::where('formulir_id', $formulir->id)->get();

            $this->line('FORM NUMBER : ' . $formulir->form_number . ' DELETED');

            Inventory::where('formulir_id', $formulir->id)->delete();

            foreach ($inventories as $inventory) {
                $qty += $inventory->quantity;
            }
        }

        $formulirs = Formulir::where('formulirable_type', '=', TransferItem::class)
            ->whereNotNull('form_number')
            ->whereNull('canceled_at')
            ->where('approval_status', '=', 1)
            ->where('form_date', '>=', '2025-05-01 00:00:00')
            ->get();

        foreach ($formulirs as $formulir) {
            $qty = 0;

            $this->line('FORM NUMBER : ' . $formulir->form_number . ' ADDED');

            $transfer_item = TransferItem::where('formulir_id', $formulir->id)->first();

            Inventory::where('formulir_id', $formulir->id)->delete();

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
                $inventory->quantity = $transfer_item_detail->qty_send;
                $inventory->price = $transfer_item_detail->cogs;
                $inventory->quantity = $transfer_item_detail->qty_received;
                if ($transfer_item_detail->qty_received > 0) {
                    $inventory_helper = new InventoryHelper($inventory);
                    $inventory_helper->in();
                }
            }

            $inventories = Inventory::where('formulir_id', $formulir->id)->get();

            foreach ($inventories as $inventory) {
                $qty += $inventory->quantity;
            }

            if ($qty != 0) {
                $this->line($formulir->form_number . ' = ' . $qty . ' = ' . $formulir->id);
            }
        }

        \DB::commit();
    }
}
