<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Inventory;
use Point\PointAccounting\Models\CutOffAccountDetail;
use Point\PointAccounting\Models\CutOffInventoryDetail;
use Point\PointAccounting\Models\CutOffPayableDetail;
use Point\Framework\Models\Journal;

class InsertCutoff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:insert-cutoff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'insert cutoff';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('insert cutoff');

        \DB::beginTransaction();

        // Get all items
        $cinventories = CutOffInventoryDetail::all();

        foreach ($cinventories as $cinventory) {
            $inventory = new Inventory;
            $inventory->form_date = "2024-04-30 00:00:00";
            $inventory->formulir_id = 249;
            $inventory->warehouse_id = $cinventory->warehouse_id;
            $inventory->item_id = $cinventory->subledger_id;
            
            if ($cinventory->amount == 0) {
                $inventory->price =  0;
                $inventory->cogs =  0;
            } else {
                $inventory->price =  $cinventory->amount / $cinventory->stock;
                $inventory->cogs =  $cinventory->amount / $cinventory->stock;
            }
            $inventory->quantity = $cinventory->stock;
            $inventory->total_quantity = $cinventory->stock;
            $inventory->total_value = $cinventory->amount;
            $inventory->save();

            $item = Item::find($cinventory->subledger_id);

            $journal = new Journal();
            $journal->form_date = "2024-04-30 00:00:00";
            $journal->coa_id = $item->account_asset_id;
            $journal->description = "Cut Off Inventory";
            $journal->debit = $cinventory->amount;
            $journal->form_journal_id = 249;
            $journal->form_reference_id;
            $journal->subledger_id = $item->id;
            $journal->subledger_type = get_class(new Item());
            $journal->save();
        }

        $caccounts = CutOffAccountDetail::all();

        foreach ($caccounts as $caccount) {
            if ($caccount->coa->has_subledger == 0) {
                $journal = new Journal();
                $journal->form_date = "2024-04-30 00:00:00";
                $journal->coa_id = $caccount->coa_id;
                $journal->description = "Cut Off";
                $journal->debit = $caccount->debit;
                $journal->credit = $caccount->credit;
                $journal->form_journal_id = 249;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }    
        }
        
        $cpayables = CutOffAccountDetail::all();

        foreach ($cpayables as $cpayable) {
            $journal = new Journal();
            $journal->form_date = "2024-04-30 00:00:00";
            $journal->coa_id = $cpayable->coa_id;
            $journal->description = "Cut Off";
            $journal->credit = $cpayable->amount;
            $journal->form_journal_id = 249;
            $journal->form_reference_id;
            $journal->subledger_id = $cpayable->subledger_id;
            $journal->subledger_type = get_class(new Person());
            $journal->save();
        }
        
        \DB::commit();
    }
}