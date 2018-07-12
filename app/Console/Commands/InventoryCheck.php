<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\PointAccounting\Models\MemoJournal;

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
        $memoJournals = MemoJournal::all();
        foreach ($memoJournals as $memoJournal) {
            foreach ($memoJournal->detail as $detail) {
                if ($detail->coa->category->name === 'Inventories' || $detail->coa->name === 'BEBAN POKOK PENJUALAN') {
                    Journal::where('form_journal_id', $memoJournal->formulir_id)->where('coa_id', $detail->coa_id)->delete();
                    $detail->delete();
                }
            }
        }

        $journals = Journal::where('coa_id', 8)->get();
        foreach ($journals as $journal) {
            $inventory = Inventory::where('formulir_id', $journal->form_journal_id)->get();

            if ($inventory->count() == 0) {
                $this->line('JOURNAL: ' . $journal->formulir->form_number . ', ID: ' . $journal->id . ', TOTAL: ' . ($journal->debit + $journal->credit));
                $journal->delete();
            }
        }

        $inventories = Inventory::all();
        $sumI = 0;
        foreach ($inventories as $inventory) {
            $journal = Journal::where('form_journal_id', $inventory->formulir_id)->get();

            if ($journal->count() == 0) {
                $this->line('INVENTORY: ' . $inventory->formulir->form_number . ', ID: ' . $inventory->id . ', TOTAL: ' . $inventory->total_value);
                $sumI += $inventory->total_value;

                $this->line('Fixing ' . $inventory->id);
                $journal = new Journal;
                $journal->coa_id = 8;
                $journal->debit = $inventory->total_value;
                $journal->form_date = $inventory->form_date;
                $journal->description = 'Cut Off';
                $journal->form_journal_id = 111;
                $journal->form_reference_id;
                $journal->subledger_id = $inventory->item_id;
                $journal->subledger_type = get_class(new Item());
                $journal->save();
            }
        }

        $this->line('SUM INVENTORY: ' . $sumI);
    }
}
