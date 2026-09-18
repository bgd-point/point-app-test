<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Journal;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockCorrection\StockCorrectionItem;
use Point\PointInventory\Helpers\StockCorrectionHelper;
use Point\PointAccounting\Models\MemoJournal;
use Point\PointAccounting\Models\MemoJournalDetail;
use Point\PointAccounting\Helpers\MemoJournalHelper;

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
          $corrections = \DB::table('journal')
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

          $form_date = '2026-07-31 23:59:59';
          $form_number = FormulirHelper::number('point-accounting-memo-journal', $form_date);

          $formulir = new Formulir;
          $formulir->form_date = $form_date;
          $formulir->created_at = $form_date;
          $formulir->updated_at = $form_date;
          $formulir->form_number = $form_number['form_number'];
          $formulir->form_raw_number = $form_number['raw'];
          $formulir->notes = 'Koreksi Journal Sediaan 2026-07-31';
          $formulir->approval_to = 1;
          $formulir->approval_status = 1;
          $formulir->approval_message = '';
          $formulir->created_by = 1;
          $formulir->updated_by = 1;
          if (!$formulir->save()) {
              gritter_error('create has been failed', false);
          }

          $memo_journal = new MemoJournal;
          $memo_journal->formulir_id = $formulir->id;
          $memo_journal->debit = 0;
          $memo_journal->credit = 0;
          $memo_journal->save();

          $total = 0;
          foreach ($corrections as $correction) {
            $this->comment($correction->coa_name . ' > ' . $correction->item_name . ' = ' . $correction->balance);

            $memo_journal_detail = new MemoJournalDetail;
            $memo_journal_detail->memo_journal_id = $memo_journal->id;
            $memo_journal_detail->coa_id = $correction->coa_id;
            $memo_journal_detail->description = 'Koreksi Journal Sediaan 2026-07-31';
            $memo_journal_detail->debit = 0;
            $memo_journal_detail->credit = $correction->balance;
            $memo_journal_detail->form_journal_id = $formulir->id;
            $memo_journal_detail->form_reference_id = '';
            $memo_journal_detail->subledger_id = $correction->subledger_id;
            $memo_journal_detail->subledger_type = 'Point\Framework\Models\Master\Item';
            $memo_journal_detail->save();

            $memo_journal_detail = new MemoJournalDetail;
            $memo_journal_detail->memo_journal_id = $memo_journal->id;
            $memo_journal_detail->coa_id = 278;
            $memo_journal_detail->description = 'Koreksi Journal Sediaan 2026-07-31';
            $memo_journal_detail->debit = $correction->balance;
            $memo_journal_detail->credit = 0;
            $memo_journal_detail->form_journal_id = $formulir->id;
            $memo_journal_detail->form_reference_id = $formulir->id;
            $memo_journal_detail->subledger_id = $correction->subledger_id;
            $memo_journal_detail->subledger_type = 'Point\Framework\Models\Master\Item';
            $memo_journal_detail->save();

            $total += $correction->balance;
          }

          $memo_journal->debit = $total;
          $memo_journal->credit = $total;
          $memo_journal->save();

          MemoJournalHelper::addToJournal($memo_journal);
        }

        \DB::commit();
    }
}
