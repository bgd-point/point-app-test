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

        $transferItems = TransferItem::join('formulir', 'formulir.id', '=', 'journal.form_journal_id')->get();            

        foreach($transferItems as $transferItem) {
            if ($transferItem->received_date) {
                $transferItem->received_date = $transferItem->formulir->updated_at;
                $transferItem->save();

                $journals = Journal::where('form_journal_id', '=', $transferItem->formulir->id)
                    ->get();
                
                foreach($journals as $journal) {
                    $journal->form_date = $transferItem->received_date;
                    $journal->save();
                }       
            }
        }

        \DB::commit();
    }
}