<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Journal;
use Point\PointManufacture\Models\InputProcess;
use Point\PointManufacture\Models\OutputProcess;
use Point\Framework\Models\Master\Item;

class RecalculateTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'recalculate';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        self::fixOutput();
    }

    public static function fixInput()
    {
        $inputs = InputProcess::join('formulir', 'point_manufacture_input.formulir_id', '=', 'formulir.id')->get();

        foreach ($inputs as $input) {

        }

        // foreach ($input_approval->material as $material) {
        //     $inventory = new Inventory;
        //     $inventory->formulir_id = $input_approval->formulir->id;
        //     $inventory->item_id = $material->material_id;
        //     $inventory->quantity = $material->quantity * InputMaterial::unit($material->material_id);
        //     $inventory->cogs = InventoryHelper::getCostOfSales(date('Y-m-d H:i:s'), $material->material_id, $material->warehouse_id);
        //     $inventory->price = $inventory->cogs;
        //     $inventory->form_date = date('Y-m-d H:i:s');
        //     $inventory->warehouse_id = $material->warehouse_id;

        //     $inventory_helper = new InventoryHelper($inventory);
        //     $inventory_helper->out();

        //     self::addJournalInput($inventory);
        // }
    }

    public static function fixOutput()
    {
        $outputs = OutputProcess::join('formulir', 'point_manufacture_output.formulir_id', '=', 'formulir.id')
            ->where('formulir.approval_status', 1)
            ->where('formulir.form_status', 1)
            ->get();

        foreach ($outputs as $output) {
            Inventory::where('formulir_id', $output->formulir_id)->delete();
            Journal::where('form_journal_id', $output->formulir_id)->delete();

            $value = Journal::where('form_journal_id', $output->input->formulir_id)->sum('debit');

            $totalQty = 0;
            foreach ($output->product as $product) {
                $totalQty += (float) $product->quantity;
            }

            if ($totalQty == 0) {
                $this->comment('zero: ' . $output->id);
                $cogs_product = 0;
            } else {
                $cogs_product = $value / $totalQty;
            }


            foreach ($output->product as $product) {
                $inventory = new Inventory();
                $inventory->formulir_id = $output->formulir->id;
                $inventory->form_date = $output->formulir->approval_at;
                $inventory->warehouse_id = $product->warehouse_id;
                $inventory->item_id = $product->product_id;
                $inventory->quantity = $product->quantity;
                $inventory->price = $cogs_product;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();

                self::addJournalOutput($inventory, $totalQty);
            }
        }
    }

    public static function addJournalInput($inventory)
    {
        // JOURNAL #1 of #2
        $journal = new Journal();
        $journal->form_date = $inventory->formulir->approval_at;
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
        $journal->form_date = $inventory->formulir->approval_at;
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

    public static function addJournalOutput($inventory, $totalInv)
    {
        // JOURNAL #2 of #2
        $output = OutputProcess::where('formulir_id', $inventory->formulir_id)->first();
        $work_in_process_account_id = JournalHelper::getAccount('manufacture process', 'work in process');
        $cjournals = Journal::where('form_journal_id', $output->input->formulir_id)
            ->where('coa_id', $work_in_process_account_id)
            ->select('journal.*')
            ->get();
        
        $vals = 0;

        foreach ($cjournals as $cjournal) {
            $journal = new Journal();
            $journal->form_date = $inventory->formulir->created_at;
            $journal->coa_id = $work_in_process_account_id;
            $journal->description = 'Manufacture output process ' . $inventory->item->codeName;
            $journal->debit = 0;
            $journal->credit = abs($cjournal->debit) * $inventory->quantity / $totalInv;
            $journal->form_journal_id = $inventory->formulir->id;
            $journal->form_reference_id;
            $journal->subledger_id = $cjournal->subledger_id;
            $journal->subledger_type = get_class($inventory->item);
            $journal->save();

            $vals += $journal->credit;
        }

        // JOURNAL #1 of #2
        $journal = new Journal();
        $journal->form_date = $inventory->formulir->created_at;
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