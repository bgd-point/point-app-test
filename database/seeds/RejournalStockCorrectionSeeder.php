<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\PointInventory\Helpers\StockCorrectionHelper;
use Point\PointInventory\Models\StockCorrection\StockCorrection;

class RejournalStockCorrectionSeeder extends Seeder
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
        $journal = Journal::whereIn('form_journal_id', $list_stock_correction)->delete();
        $inventory = Inventory::whereIn('formulir_id', $list_stock_correction)->delete();
        $list_stock_correction = StockCorrection::whereIn('formulir_id', $list_stock_correction)->get();
        foreach ($list_stock_correction as $stock_correction) {
        	StockCorrectionHelper::approve($stock_correction);
        	StockCorrectionHelper::updateJournal($stock_correction);
        	JournalHelper::checkJournalBalance($stock_correction->formulir_id);
        }
    }
}