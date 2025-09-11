<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\PointAccounting\Models\MemoJournal;
use Point\PointInventory\Models\TransferItem\TransferItem;

class InventoryCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:inventory-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check inventory and general ledger value';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('STARTING');

        $transferItems = TransferItem::join('formulir', 'formulir.id', '=', 'point_inventory_transfer_item.formulir_id')
            ->where('formulir.approval_status', 1)
            ->where('formulir.form_status', 1)
            ->select('point_inventory_transfer_item.*')
            ->get();

        foreach ($transferItems as $transferItem) {
            $inv = Inventory::where('formulir_id', '=', $transferItem->formulir_id)->sum('quantity');
            if ($inv > 0) {
                $this->line($transferItem->formulir_id . ' - Problem TI');
            }
        }

        $this->line('ENDING');
    }
}
