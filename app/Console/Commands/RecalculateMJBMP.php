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

class RecalculateMJBMP extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * @var string
     */
    protected $signature = 'dev:recalculate:mj-bmp';

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

        $coas = Coa::orderBy('coa_number', 'asc')->get();

        $form_date = '2026-06-30 23:59:00';
        $form_number = FormulirHelper::number('point-accounting-memo-journal', $form_date);

        $formulir = new Formulir;
        $formulir->form_date = $form_date;
        $formulir->created_at = $form_date;
        $formulir->updated_at = $form_date;
        $formulir->form_number = $form_number['form_number'];
        $formulir->form_raw_number = $form_number['raw'];
        $formulir->notes = 'Koreksi Journal Sediaan 2026-06-30';
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


        foreach ($coas as $coa) {

            $journals = Journal::where('coa_id', $coa->id)
                ->where('form_date', '<=' , '2026-06-30 23:59:50')
                ->selectRaw('subledger_id, SUM(debit - credit) as balance')
                ->groupBy('subledger_id')
                ->get();
            
            $total = 0;
            foreach ($journals as $correction) {
                // $this->comment('('.$coa->category->position->debit.')' . $coa->coa_number . ' | ' . $coa->name . ' | ' . $correction->subledger_id . ' = ' . $correction->balance);
                $positionDebit = (int) $coa->category->position->debit;
                // COA SEDIAAN
                $memo_journal_detail = new MemoJournalDetail;
                $memo_journal_detail->memo_journal_id = $memo_journal->id;
                $memo_journal_detail->coa_id = $coa->id;
                $memo_journal_detail->description = 'Koreksi Cut Off 2026-06-30';
                $memo_journal_detail->debit = $positionDebit === 1 ? $correction->balance * -1 : 0;
                $memo_journal_detail->credit = $positionDebit === 0 ? $correction->balance * -1 : 0;
                $memo_journal_detail->form_journal_id = $formulir->id;
                $memo_journal_detail->form_reference_id = null;
                $memo_journal_detail->subledger_id = $correction->subledger_id ?? NULL;
                $memo_journal_detail->subledger_type = $correction->subledger_id ? 'Point\Framework\Models\Master\Item' : NULL;
                $memo_journal_detail->save();

                $journal = new Journal;
                $journal->form_date = $memo_journal->formulir->form_date;
                $journal->coa_id = $memo_journal_detail->coa_id;
                $journal->description = $memo_journal_detail->description;
                $journal->debit = $memo_journal_detail->debit;
                $journal->credit = $memo_journal_detail->credit;
                $journal->form_journal_id = $memo_journal->formulir_id;
                $journal->form_reference_id = $memo_journal_detail->form_reference_id ?: null;
                $journal->subledger_id = $memo_journal_detail->subledger_id ?: null;
                $journal->subledger_type = $memo_journal_detail->subledger_type ?: null;

                $journal->save();

                $this->comment('('.$coa->category->position->debit.')' . $coa->coa_number . ' | ' . $coa->name . ' | ' . $memo_journal_detail->debit . ' = ' . $memo_journal_detail->credit);
                $total += $correction->balance;
            }

            $memo_journal->debit = $total;
            $memo_journal->credit = $total;
            $memo_journal->save();
        }

        \DB::commit();
    }
}
