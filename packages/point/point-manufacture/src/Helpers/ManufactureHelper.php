<?php

namespace Point\PointManufacture\Helpers;

use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\ItemUnit;
use Point\PointManufacture\Models\Formula;
use Point\PointManufacture\Models\FormulaMaterial;
use Point\PointManufacture\Models\FormulaProduct;
use Point\PointManufacture\Models\InputMaterial;
use Point\PointManufacture\Models\InputProcess;
use Point\PointManufacture\Models\InputProduct;
use Point\PointManufacture\Models\OutputProcess;
use Point\PointManufacture\Models\OutputProduct;

class ManufactureHelper
{
    public static function createFormula($request, $formulir)
    {
        $formula = new Formula;
        $formula->formulir_id = $formulir->id;
        $formula->process_id = $request->input('process_id');
        $formula->name = $request->input('name');
        $formula->save();

        // Finished goods
        for ($i = 0; $i < count($request->input('product_id')); $i++) {
            $finished_goods = new FormulaProduct;
            $finished_goods->formula_id = $formula->id;
            $finished_goods->product_id = $request->input('product_id')[$i];
            $finished_goods->warehouse_id = $request->input('product_warehouse_id')[$i];
            $finished_goods->quantity += number_format_db($request->input('product_quantity')[$i]);
            $unit = ItemUnit::where('item_id',$request->input('product_id')[$i])->first();
            $finished_goods->unit = $unit->name;
            $converter = number_format_db($unit->converter);
            $finished_goods->converter = $converter;
            $finished_goods->save();
        }

        // Raw material
        for ($i = 0; $i < count($request->input('material_id')); $i++) {
            $raw_material = new FormulaMaterial;
            $raw_material->formula_id = $formula->id;
            $raw_material->material_id = $request->input('material_id')[$i];
            $raw_material->warehouse_id = $request->input('material_warehouse_id')[$i];
            $raw_material->quantity += number_format_db($request->input('material_quantity')[$i]);
            $unit = ItemUnit::where('item_id',$request->input('material_id')[$i])->first();
            $raw_material->unit = $unit->name;
            $raw_material->converter = number_format_db($unit->converter);
            $raw_material->save();
        }

        return $formula;
    }

    public static function createInput($request, $formulir)
    {
        $product_id = $request->input('product_id');
        $quantity = $request->input('product_quantity');
        $warehouse_product = $request->input('product_warehouse_id');

        $input = new InputProcess;
        $input->formulir_id = $formulir->id;
        $input->process_id = $request->input('process_id');
        $input->machine_id = $request->input('machine_id');
        $input->formula_id = $request->input('formula_id') ? : null;
        $input->save();

        // Raw material
        $material_id = $request->input('material_id');
        $warehouse_id = $request->input('material_warehouse_id');
        $material_quantity = $request->input('material_quantity');

        for ($i = 0; $i < count($material_id); $i++) {
            $raw_material = new InputMaterial;
            $raw_material->input_id = $input->id;
            $raw_material->material_id = $material_id[$i];
            $raw_material->warehouse_id = $warehouse_id[$i];
            $raw_material->quantity = number_format_db($material_quantity[$i]);
            $unit = ItemUnit::where('item_id', $material_id[$i])->first();
            $raw_material->unit = $unit->name;
            $raw_material->converter = number_format_db($unit->converter);
            $raw_material->cogs = InventoryHelper::getCostOfSales(date('Y-m-d H:i:s'), $raw_material->material_id, $raw_material->warehouse_id) * $raw_material->quantity;
            $raw_material->save();
        }

        // Finished goods
        for ($i = 0; $i < count($product_id); $i++) {
            $finished_goods = new InputProduct;
            $finished_goods->input_id = $input->id;
            $finished_goods->product_id = $product_id[$i];
            $finished_goods->quantity = number_format_db($quantity[$i]);
            $unit = ItemUnit::where('item_id', $product_id[$i])->first();
            $finished_goods->unit = $unit->name;
            $converter = number_format_db($unit->converter);
            $finished_goods->converter = $converter;
            $finished_goods->warehouse_id = $warehouse_product[$i];
            $finished_goods->save();
        }

        return $input;
    }

    public static function approveInput($input_approval)
    {
        foreach ($input_approval->material as $material) {
            $inventory = new Inventory;
            $inventory->formulir_id = $input_approval->formulir->id;
            $inventory->item_id = $material->material_id;
            $inventory->quantity = $material->quantity * InputMaterial::unit($material->material_id);
            $inventory->cogs = InventoryHelper::getCostOfSales(date('Y-m-d H:i:s'), $material->material_id, $material->warehouse_id);
            $inventory->price = $inventory->cogs;
            $inventory->form_date = date('Y-m-d H:i:s');
            $inventory->warehouse_id = $material->warehouse_id;

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->out();

            self::addJournalInput($inventory);
        }
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
            $totalInv += (float) $input_product->quantity;
        }
        foreach ($input_process->product as $input_product) {
            $quantity = number_format_db($request->input('quantity_output')[$i]);
            $inventory = new Inventory();
            $inventory->formulir_id = $formulir->id;
            $inventory->form_date = $formulir->created_at;
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
