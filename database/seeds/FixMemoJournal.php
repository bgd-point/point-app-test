<?php

use Illuminate\Database\Seeder;
use Point\PointAccounting\Helpers\MemoJournalHelper;

class FixMemoJournal extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $list_memo_journal = \Point\PointAccounting\Models\MemoJournal::all();
        foreach ($list_memo_journal as $memo_journal) {
            $journal = \Point\Framework\Models\Journal::where('form_journal_id', $memo_journal->formulir_id)->first();
            if(!$journal) {
                MemoJournalHelper::addToJournal($memo_journal);
            }
        }

        \DB::commit();
    }
}
