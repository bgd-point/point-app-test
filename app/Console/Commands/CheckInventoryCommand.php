<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;

class CheckInventoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:check-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Inventory';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $manufactures = \Point\PointManufacture\Models\InputProcess::join('formulir', 'point_manufacture_input.formulir_id', '=', 'formulir.id')
            ->where('formulir.form_date', '>', '2018-11-15 09:00:00')
            ->whereNotNull('formulir.form_number')
            ->where('process_id', 14)
            ->select('point_manufacture_input.*')
            ->get();

        foreach ($manufactures as $manufacture) {
            DB::beginTransaction();
            Journal::where('form_journal_id', $manufacture->formulir_id)->delete();

            // Update InputMaterial
            foreach ($manufacture->material as $material) {
                $inventory = Inventory::where('formulir_id', $manufacture->formulir_id)->where('item_id', $material->material_id)->first();
                if ($material->material_id == 42) {
                    $material->material_id = 115;
                    $inventory->item_id = 115;
                } else if ($material->material_id == 9 || $material->material_id == 89) {
                    $material->warehouse_id = 2;
                    $inventory->warehouse_id = 2;
                }

                $cogs = InventoryHelper::getCostOfSales($manufacture->formulir->form_date,
                    $material->material_id,
                    $material->warehouse_id);

                $material->cogs = $cogs * $material->quantity;
                $material->save();

                $inventory->price = $cogs;
                $inventory->save();

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
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }

            DB::commit();
        }
    }
}
