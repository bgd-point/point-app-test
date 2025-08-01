<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\PointManufacture\Helpers\ManufactureHelper;
use Point\PointManufacture\Models\OutputProcess;
use Point\Framework\Models\Master\Item;

class RejournalIO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:rejournal-io';

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

    public $coaDalamProses = 0;
    public $coaSetengahJadi = 0;

    public function handle()
    {
        $this->comment('recalculating inventory');
        \DB::beginTransaction();

        // $this->fixCoa();
        $this->fixSubledger();

        \DB::commit();
    }

    public function fixCoa() {
        $items = Item::where('account_asset_id', $this->coaDalamProses)->get();

        foreach ($items as $item) {
            $this->comment('item-'.$item->id.' = ' . $item->code . ' ' . $item->name);
            $item->account_asset_id = $this->coaSetengahJadi;
            $item->save();

            $journals = Journal::join('formulir', 'formulir.id', '=', 'journal.form_journal_id')
                ->where('subledger_type', 'Point\Framework\Models\Master\Item')
                ->where('subledger_id', $item->id)
                ->where('coa_id', $this->coaDalamProses)
                ->select('journal.*')
                ->get();

            foreach($journals as $journal) {
                $this->comment('journal-'.$journal->form_date.' = ' . $journal->description);
                $journal->coa_id = $this->coaSetengahJadi;
                $journal->save();
            }
        }
    }

    public function fixSubledger() {
        $this->comment('================================================');
        $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
            ->where('formulir.form_number', 'like', 'INPUT/%')
            ->select('inventory.*')
            ->groupBy('inventory.formulir_id')
            ->get();

        foreach ($inventories as $inventory) {
            Journal::where('form_journal_id', $inventory->formulir_id)->delete();
            $invs = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
                ->where('formulir.id', $inventory->formulir_id)
                ->select('inventory.*')
                ->get();
            foreach($invs as $inv) {
                $this->addJournalInput($inv);
            }
        }
        
        $inventories = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
        ->where('formulir.form_number', 'like', 'OUTPUT/%')
        ->select('inventory.*')
        ->groupBy('inventory.formulir_id')
        ->get();

        foreach ($inventories as $inventory) {
            Journal::where('form_journal_id', $inventory->formulir_id)->delete();
            $invs = Inventory::join('formulir', 'formulir.id', '=', 'inventory.formulir_id')
                ->where('formulir.id', $inventory->formulir_id)
                ->select('inventory.*')
                ->get();
            $ttotal = 0;
            foreach($invs as $inv) {
                $ttotal += (float) $inv->quantity;
            }
            foreach($invs as $inv) {
                $this->addJournalOutput($inv, $ttotal);
            }
        }
    }

    public function addJournalInput($inventory)
    {
        // JOURNAL #1 of #2
        $journal = new Journal();
        $journal->form_date = $inventory->formulir->form_date;
        $journal->coa_id = $inventory->item->account_asset_id;
        $journal->description = 'Manufacture input process ' . $inventory->item->codeName;
        $journal->debit = 0;
        $journal->credit = abs($inventory->quantity * $inventory->price);
        $journal->form_journal_id = $inventory->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id = $inventory->item_id;
        $journal->subledger_type = get_class($inventory->item);
        $journal->save();

        // JOURNAL #2 of #2
        $work_in_process_account_id = JournalHelper::getAccount('manufacture process', 'work in process');

        $journal = new Journal();
        $journal->form_date = $inventory->formulir->form_date;
        $journal->coa_id = $work_in_process_account_id;
        $journal->description = 'Manufacture input process ' . $inventory->item->codeName;
        $journal->debit = abs($inventory->quantity * $inventory->price);
        $journal->credit = 0;
        $journal->form_journal_id = $inventory->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id = $inventory->item_id;
        $journal->subledger_type = get_class($inventory->item);
        $journal->save();
    }

    public function addJournalOutput($inventory, $ttotal)
    {
        // JOURNAL #1 of #2
        $work_in_process_account_id = JournalHelper::getAccount('manufacture process', 'work in process');
        $output = OutputProcess::where('formulir_id', $inventory->formulir_id)->first();
        $cjournals = Journal::where('form_journal_id', $output->input->formulir_id)
            ->where('coa_id', $work_in_process_account_id)
            ->select('journal.*')
            ->get();
        
        $vals = 0;
        foreach ($cjournals as $cjournal) {
            $journal = new Journal();
            $journal->form_date = $inventory->formulir->form_date;
            $journal->coa_id = $work_in_process_account_id;
            $journal->description = 'Manufacture output process ' . $inventory->item->codeName;
            $journal->debit = 0;
            $journal->credit = abs($cjournal->debit) * $inventory->quantity / $ttotal;
            $journal->form_journal_id = $inventory->formulir->id;
            $journal->form_reference_id;
            $journal->subledger_id = $cjournal->subledger_id;
            $journal->subledger_type = get_class($inventory->item);
            $journal->save();

            $vals += $journal->credit;
        }

        // JOURNAL #2 of #2
        $journal = new Journal();
        $journal->form_date = $inventory->formulir->form_date;
        $journal->coa_id = $inventory->item->account_asset_id;
        $journal->description = 'Manufacture output process ' . $inventory->item->codeName;
        $journal->debit = $vals;
        $journal->credit = 0;
        $journal->form_journal_id = $inventory->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id = $inventory->item_id;
        $journal->subledger_type = get_class($inventory->item);
        $journal->save();
    }
}
