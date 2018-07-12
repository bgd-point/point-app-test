<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;

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
        $inventories = Inventory::all();
        foreach ($inventories as $inventory) {
            $journal = Journal::where('form_journal_id', $inventory->formulir_id)->get();

            if ($journal->count() == 0) {
                $this->line('INVENTORY: ' . $inventory->formulir->form_number . ', ID: ' . $inventory->id . ', TOTAL: ' . $inventory->total_value);
            }
        }

        $journals = Journal::where('coa_id', 8)->get();
        foreach ($journals as $journal) {
            $inventory = Inventory::where('formulir_id', $journal->form_journal_id)->get();

            if ($inventory->count() == 0) {
                $this->line('JOURNAL: ' . $journal->formulir->form_number . ', ID: ' . $journal->id . ', TOTAL: ' . ($journal->debit + $journal->credit));
            }
        }
    }
}
