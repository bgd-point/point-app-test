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
            
        //   $corrections = \DB::table('journal')
        //     ->join('coa', 'coa.id', '=', 'journal.coa_id')
        //     ->join('item', 'item.id', '=', 'journal.subledger_id')
        //     ->select([
        //         'journal.coa_id',
        //         'coa.name as coa_name',
        //         'journal.subledger_id',
        //         'item.name as item_name',
        //         \DB::raw('SUM(journal.debit - journal.credit) AS balance'),
        //     ])
        //     ->where('journal.coa_id', $coa->id)
        //     ->where('journal.form_date', '<', '2026-08-01')
        //     ->where('journal.subledger_type', '=', 'Point\\Framework\\Models\\Master\\Item')
        //     ->groupBy([
        //         'journal.coa_id',
        //         'coa.name',
        //         'journal.subledger_id',
        //         'item.name',
        //     ])
        //     ->orderBy('journal.subledger_id')
        //     ->get();

          $corrections = Journal::join('coa', 'coa.id', '=', 'journal.coa_id')
            ->leftJoin('item', 'item.id', '=', 'journal.subledger_id')
            ->where('journal.form_date', '<', '2026-08-01')
            ->where('journal.coa_id', $coa->id)
            ->selectRaw('
                coa.id AS coa_id,
                coa.name AS coa_name,
                item.name AS item_name,
                journal.subledger_id,
                SUM(journal.debit) - SUM(journal.credit) AS balance
            ')
            ->groupBy(
                'coa.id',
                'coa.name',
                'journal.subledger_id',
                'item.name'
            )
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
          $formulir->notes = 'Koreksi Journal Sediaan ' . $coa->name . ' 2026-07-31';
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
            $this->comment($correction->coa_name . ' > ' . $correction->subledger_id . ' = ' . $correction->balance);

            $balance = (float) $correction->balance;

            // 1
            if ($correction->subledger_id === 86) {
                $correction->balance = 27854765770.8788;
            }
            // 2
            if ($correction->subledger_id === 250) {
                $correction->balance = -538141449716.762;
            }
            // 3
            if ($correction->subledger_id === 347) {
                $correction->balance = -16929409626.0452;
            }
            // 4
            if ($correction->subledger_id === 370) {
                $correction->balance = -54186275474.4436;
            }
            // 5
            if ($correction->subledger_id === 390) {
                $correction->balance = -49877179805.4726;
            }
            // 6
            if ($correction->subledger_id === 391) {
                $correction->balance = -431660894971.262;
            }
            // 7
            if ($correction->subledger_id === 399) {
                $correction->balance = -12304058049.1472;
            }
            // 8
            if ($correction->subledger_id === 416) {
                $correction->balance = -383138068082.629;
            }
            // 9
            if ($correction->subledger_id === 448) {
                $correction->balance = -145941577303.706;
            }
            // 10
            if ($correction->subledger_id === 449) {
                $correction->balance = -2733433589809.89;
            }
            // 11
            if ($correction->subledger_id === 481) {
                $correction->balance = -113530669750.391;
            }
            // 12
            if ($correction->subledger_id === 628) {
                $correction->balance = 18821555657.1335;
            }

            // COA SEDIAAN
            $memo_journal_detail = new MemoJournalDetail;
            $memo_journal_detail->memo_journal_id = $memo_journal->id;
            $memo_journal_detail->coa_id = $correction->coa_id;
            $memo_journal_detail->description = 'Koreksi Journal Sediaan ' . $correction->coa_name . ' 2026-07-31';
            $memo_journal_detail->debit = 0;
            $memo_journal_detail->credit = $correction->balance;
            $memo_journal_detail->form_journal_id = $formulir->id;
            $memo_journal_detail->form_reference_id = null;
            $memo_journal_detail->subledger_id = $correction->subledger_id ?? NULL;
            $memo_journal_detail->subledger_type = $correction->subledger_id ? 'Point\Framework\Models\Master\Item' : NULL;
            $memo_journal_detail->save();

            // COA SELISIH KOREKSI
            $memo_journal_detail = new MemoJournalDetail;
            $memo_journal_detail->memo_journal_id = $memo_journal->id;
            $memo_journal_detail->coa_id = 50;
            $memo_journal_detail->description = 'Koreksi Journal Sediaan ' . $correction->coa_name . ' 2026-07-31';
            $memo_journal_detail->debit = $correction->balance;
            $memo_journal_detail->credit = 0;
            $memo_journal_detail->form_journal_id = $formulir->id;
            $memo_journal_detail->form_reference_id = null;
            $memo_journal_detail->subledger_id = $correction->subledger_id ?? NULL;
            $memo_journal_detail->subledger_type = $correction->subledger_id ? 'Point\Framework\Models\Master\Item' : NULL;
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
