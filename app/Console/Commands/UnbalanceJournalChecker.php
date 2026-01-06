<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Journal;

class UnbalanceJournalChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'journal:check-unbalance';

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
        $journals = Journal::groupBy('form_journal_id');
        foreach ($journals->where('form_date', '>=', '2025-11-01')->get() as $journal) {
            $debit = Journal::where('form_journal_id', $journal->form_journal_id)->sum('debit');
            $credit = Journal::where('form_journal_id', $journal->form_journal_id)->sum('credit');

            if ($debit != $credit) {
                $this->comment($journal->form_journal_id . ' ' . $journal->formulir->form_number . ' ' . $journal->formulir->formulirable_type . ' ' . $debit . ' ' . $credit);
            }
        }

        $this->comment('second attempt');

        $journals = Journal::select(
            'form_journal_id',
            DB::raw('SUM(debit) as total_debit'),
            DB::raw('SUM(credit) as total_credit')
        )
        ->where('form_date', '>=', '2025-11-01')
        ->groupBy('form_journal_id')
        ->havingRaw('ABS(SUM(debit) - SUM(credit)) > 0.01')
        ->get();

        foreach ($journals as $journal) {
            $this->comment(
                "{$journal->form_journal_id} | Debit: {$journal->total_debit} | Credit: {$journal->total_credit}"
            );
        }
    }
}
