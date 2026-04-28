<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
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
        Journal::where('description', 'Pembulatan')
            ->whereNotNull('subledger_type')
            ->delete();
        self::fixInput();
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

    public static function createOutput($request, $formulir)
    {
        $input_process = InputProcess::find($request->input('input_id'));

        $output = new OutputProcess;
        $output->formulir_id = $formulir->id;
        $output->machine_id = $input_process->machine_id;
        $output->input_id = $input_process->id;
        $output->save();

        // close this form, doesn't need approval too
        $output->formulir->form_status = 1;
        $output->formulir->approval_status = 1;
        $output->formulir->save();

        $i = 0;
        foreach ($input_process->product as $input_product) {
            $quantity = number_format_db($request->input('quantity_output')[$i]);
            $output_product = new OutputProduct;
            $output_product->output_id = $output->id;
            $output_product->warehouse_id = $input_product->warehouse_id;
            $output_product->product_id = $input_product->product_id;
            $output_product->quantity = $quantity;
            $output_product->unit = $input_product->unit;
            $output_product->converter = $input_product->converter;
            $output_product->save();
            $i++;
        }

        // SET COGS FOR PRODUCT
        $total_product_quantity = 0;
        for ($i=0; $i < count($request->input('quantity_output')); $i++) {
            $total_product_quantity += number_format_db($request->input('quantity_output')[$i]);
        }

        $total_value = Journal::where('form_journal_id', $input_process->formulir_id)->sum('debit');
        $cogs_product = $total_value / $total_product_quantity;
        
        $i = 0;
        $totalInv = 0;
        foreach ($input_process->product as $input_product) {
            $quantity = number_format_db($request->input('quantity_output')[$i]);
            $totalInv += (float) $quantity;
            $i++;
        }

        $i = 0;
        foreach ($input_process->product as $input_product) {
            $quantity = number_format_db($request->input('quantity_output')[$i]);
            $inventory = new Inventory();
            $inventory->formulir_id = $formulir->id;
            $inventory->form_date = date('Y-m-d H:i:s');
            $inventory->warehouse_id = $input_product->warehouse_id;
            $inventory->item_id = $input_product->product_id;
            $inventory->quantity = $quantity;
            $inventory->price = $cogs_product;

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->in();

            self::addJournalOutput($inventory, $totalInv);
            $i++;
        }

        // update formulir input
        formulir_lock($input_process->formulir_id, $formulir->id);
        $input_process->formulir->form_status = 1;
        $input_process->save();

        return $output;
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