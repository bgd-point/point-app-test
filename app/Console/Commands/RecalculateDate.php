<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;

class RecalculateDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:date';

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

        $formulirs = Formulir::where('form_date', '>=', '2025-06-01')->get();

        foreach ($formulirs as $formulir) {
            $inventories = Inventory::where('formulir_id', $formulir->id)->get();
            foreach ($inventories as $inventory) {
                $inventory->form_date = $formulir->form_date;
                $inventory->save();
            }

            $journals = Journal::where('form_journal_id', $formulir->id)->get();
            foreach ($journals as $journal) {
                $journal->form_date = $formulir->form_date;
                $journal->save();
            }
        }

        \DB::commit();
    }
}