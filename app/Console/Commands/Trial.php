<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Journal;

class Trial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:trial';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check unbalance journal';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $journals = Journal::where('form_date', '>=', '2025-01-01')->groupBy('form_journal_id')->get();

        $this->comment('Count: ' . $journals->count());
        foreach ($journals as $journal) {
            $debit = Journal::where('form_journal_id', '=', $journal->form_journal_id)->sum('debit');
            $credit = Journal::where('form_journal_id', '=', $journal->form_journal_id)->sum('credit');
            if ($debit !== $credit) {
                $this->comment('ID ' . $journal->form_journal_id . ' | ' . $debit . ' / ' . $credit);
            }
        }
    }
}