<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockCorrection\StockCorrectionItem;
use Point\PointInventory\Helpers\StockCorrectionHelper;

class RecalculateMJ extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * @var string
     */
    protected $signature = 'dev:recalculate:mj';

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
        $this->comment('handle inventory all');

        \DB::beginTransaction();

        $coas = Coa::where('coa_category_id', 4)->get();

        foreach($coas as $coa) {
          $journals = \DB::table('journal')
            ->join('coa', 'coa.id', '=', 'journal.coa_id')
            ->join('item', 'item.id', '=', 'journal.subledger_id')
            ->select([
                'journal.coa_id',
                'coa.name as coa_name',
                'journal.subledger_id',
                'item.name as item_name',
                \DB::raw('SUM(journal.debit - journal.credit) AS balance'),
            ])
            ->where('journal.coa_id', $coa->id)
            ->where('journal.form_date', '<', '2026-08-01')
            ->groupBy([
                'journal.coa_id',
                'coa.name',
                'journal.subledger_id',
                'item.name',
            ])
            ->orderBy('journal.subledger_id')
            ->get();

            foreach ($journal) {
              $this->comment($journal);
            }
        }

        \DB::commit();
    }
}
