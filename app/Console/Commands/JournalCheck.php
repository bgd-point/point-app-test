<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\PointAccounting\Models\MemoJournal;

class JournalCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:journal-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check journal';

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
        $journals = Journal::where('form_date', '>=', '2025-01-01')->groupBy('form_journal_id')->get();
        foreach ($journals as $journal) {
            $debit = Journal::where('form_journal_id', $journal->form_journal_id)->sum('debit');
            $credit = Journal::where('form_journal_id', $journal->form_journal_id)->sum('credit');
            if ($debit != $credit) {

                $this->line('JOURNAL: ' . $journal->formulir->form_number . ' = ' . $debit . ' != ' . $credit); );
            }
        }
    }
}
