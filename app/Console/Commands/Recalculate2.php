<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\PointManufacture\Helpers\ManufactureHelper;

class Recalculate2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate2';

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

        $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('formulir.form_number', 'like', 'INPUT/%')
            ->get();

        foreach ($inventories as $inventory) {
            Journal::where('form_journal_id', $inventory->formulir_id)->delete();
            $this->addJournalInput($inventory);
        }

        $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('formulir.form_number', 'like', 'OUTPUT/%')
            ->get();

        foreach ($inventories as $inventory) {
            Journal::where('form_journal_id', $inventory->formulir_id)->delete();
            $this->addJournalOutput($inventory);
        }

        \DB::commit();
    }

    public function addJournalInput($inventory)
    {
        // JOURNAL #1 of #2
        $journal = new Journal();
        $journal->form_date = $inventory->formulir->form_date;
        $journal->coa_id = $inventory->item->account_asset_id;
        $journal->description = 'Manufacture input process ' . $inventory->item->codeName;
        $journal->debit = 0;
        $journal->credit = abs($inventory->quantity * $inventory->price);
        $journal->form_journal_id = $inventory->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id = $inventory->item_id;
        $journal->subledger_type = get_class($inventory->item);
        $journal->save();

        // JOURNAL #2 of #2
        $work_in_process_account_id = JournalHelper::getAccount('manufacture process', 'work in process');

        $journal = new Journal();
        $journal->form_date = $inventory->formulir->form_date;
        $journal->coa_id = $work_in_process_account_id;
        $journal->description = 'Manufacture input process ' . $inventory->item->codeName;
        $journal->debit = abs($inventory->quantity * $inventory->price);
        $journal->credit = 0;
        $journal->form_journal_id = $inventory->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id = $inventory->item_id;
        $journal->subledger_type = get_class($inventory->item);
        $journal->save();
    }

    public function addJournalOutput($inventory)
    {
        // JOURNAL #1 of #2
        $journal = new Journal();
        $journal->form_date = $inventory->formulir->form_date;
        $journal->coa_id = $inventory->item->account_asset_id;
        $journal->description = 'Manufacture input process ' . $inventory->item->codeName;
        $journal->debit = abs($inventory->quantity * $inventory->price);
        $journal->credit = 0;
        $journal->form_journal_id = $inventory->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id = $inventory->item_id;
        $journal->subledger_type = get_class($inventory->item);
        $journal->save();

        // JOURNAL #2 of #2
        $work_in_process_account_id = JournalHelper::getAccount('manufacture process', 'work in process');

        $journal = new Journal();
        $journal->form_date = $inventory->formulir->form_date;
        $journal->coa_id = $work_in_process_account_id;
        $journal->description = 'Manufacture input process ' . $inventory->item->codeName;
        $journal->debit = 0;
        $journal->credit = abs($inventory->quantity * $inventory->price);
        $journal->form_journal_id = $inventory->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id = $inventory->item_id;
        $journal->subledger_type = get_class($inventory->item);
        $journal->save();
    }
}
