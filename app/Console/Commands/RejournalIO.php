<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\PointManufacture\Helpers\ManufactureHelper;
use Point\Framework\Models\Master\Item;

class RejournalIO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:rejournal-io';

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

        $this->fixCoa();
        $this->fixSubledger();
        // $this->fixOutputValue();

        \DB::commit();
    }

    public function fixOutputValue() {
        $journals = Journal::where('coa_id', 171)->get();
    }

    public function fixCoa() {
        $items = Item::where('account_asset_id', 171)->get();

        foreach ($items as $item) {
            $this->comment('item-'.$item->id.' = ' . $item->code . ' ' . $item->name);
            $item->account_asset_id = 170;
            $item->save();

            $journals = Journal::join('formulir', 'formulir.id', '=', 'journal.form_journal_id')
                ->where('subledger_type', 'Point\Framework\Models\Master\Item')
                ->where('subledger_id', $item->id)
                ->where('coa_id', 171)
                ->select('journal.*')
                ->get();

            foreach($journals as $journal) {
                $this->comment('journal-'.$journal->form_date.' = ' . $journal->description);
                $journal->coa_id = 170;
                $journal->save();
            }
        }
    }

    public function fixSubledger() {
        $this->comment('================================================');
        $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('formulir.form_number', 'like', 'INPUT/%')
            ->select('inventory.*')
            ->get();

        foreach ($inventories as $inventory) {
            if ($inventory->item->id == 615)
            $this->comment('item: ' . $inventory->item->id . ' = ' . $inventory->formulir->form_number);
            // Journal::where('form_journal_id', $inventory->formulir_id)->delete();
            // $this->addJournalInput($inventory);
        }

        $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('formulir.form_number', 'like', 'OUTPUT/%')
            ->select('inventory.*')
            ->get();

        foreach ($inventories as $inventory) {
            if ($inventory->item->id == 615)
            $this->comment('item: ' . $inventory->item->id . ' = ' . $inventory->formulir->form_number);
            // Journal::where('form_journal_id', $inventory->formulir_id)->delete();
            // $this->addJournalOutput($inventory);
        }
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
