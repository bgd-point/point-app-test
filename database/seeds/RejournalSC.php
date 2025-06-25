<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\PointInventory\Helpers\StockCorrectionHelper;
use Point\PointInventory\Models\StockCorrection\StockCorrection;

class RejournalSC extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();
        \Log::info('---- seeder stock correction started ----');
        self::stockCorrection();
        \Log::info('---- seeder stock correction finished ----');
        \DB::commit();
    }

    public function stockCorrection()
    {
        $list_stock_correction = StockCorrection::joinFormulir()->where('formulir.form_status', 1)->notArchived()->approvalApproved()->select('formulir.id')->get()->toArray();
        $journals = Journal::whereIn('form_journal_id', $list_stock_correction)->get();

        foreach ($journals as $journal) {
            $journal->form_date = $journal->formulir->form_date;
            $journal->save();
        }
    }
}
