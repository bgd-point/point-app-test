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
use Point\Framework\Models\Master\Item;
use Point\PointManufacture\Models\Formula;
use Point\PointManufacture\Models\FormulaMaterial;
use Point\PointManufacture\Models\FormulaProduct;
use Point\PointManufacture\Models\InputMaterial;
use Point\PointManufacture\Models\InputProcess;
use Point\PointManufacture\Models\InputProduct;
use Point\PointManufacture\Models\OutputProcess;
use Point\PointManufacture\Models\OutputProduct;


class RecalculateInput extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:input';

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
        $inputs = InputProcess::join('formulir', 'point_manufacture_input.formulir_id', '=', 'formulir.id')
            ->select('point_manufacture_input.*')
            ->get();

        foreach ($inputs as $input) {
            foreach ($input->material as $material) {
                Journal::where('form_journal_id', $input->formulir->id)->delete();
                Inventory::where('formulir_id', $input->formulir->id)->delete();
                \Log::info($input->id . ' = ' . $input->material);
                $inventory = new Inventory;
                $inventory->formulir_id = $input->formulir->id;
                $inventory->item_id = $material->material_id;
                $inventory->quantity = $material->quantity * InputMaterial::unit($material->material_id);
                $inventory->cogs = InventoryHelper::getCostOfSales($input->formulir->approval_at, $material->material_id, $material->warehouse_id);
                $inventory->price = $inventory->cogs;
                $inventory->form_date = $input->formulir->approval_at;
                $inventory->warehouse_id = $material->warehouse_id;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->out();

                self::addJournalInput($inventory);
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
}