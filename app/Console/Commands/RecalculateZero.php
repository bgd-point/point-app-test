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

class RecalculateZero extends Command
{
    /**
     * The name and signature of the console command.
     *
     * temporarily change sc setting journal to selisih koreksi 
     * table memo journal detail allow null to subledger id and type
     * table memo journal detail and journal: debit & credit should become decimal 20,4
     * php artisan dev:recalculate:cutoff && php artisan dev:recalculate:qty && php artisan dev:recalculate:zero && php artisan dev:recalculate:allval && php artisan dev:recalculate:all && php artisan dev:recalculate:jhppkb && php artisan dev:recalculate:mj
     * 
     * dev:recalculate:cutoff => hitung ulang dari data cutoff mirna untuk 1 agustus 2026 menggunakan Stock Correction
     * dev:recalculate:qty => fix qty data lama yang all tidak sesuai karena ada 2 item yang sama di 1 invoice
     * dev:recalculate:zero => nol kan semua value yang quantity sudah 0
     * dev:recalculate:allval => hitung ulang qty_all dan value_all untuk cogs (all warehouse)
     * dev:recalculate:all => hitung ulang qty dan value per warehouse
     * dev:recalculate:jhppkb => fix hpp per feature setelah hitung ulang
     * dev:recalculate:mj => nol kan ledger per tanggal 31 july 2026 menggunakan Memo Journal
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:zero';

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

        $inventories = Inventory::where('total_quantity_all', 0)->get();

        foreach ($inventories as $inventory) {
          $inventory->cogs = 0;
          $inventory->total_value = 0;
          $inventory->total_value_all = 0;
          $inventory->save();
        }

        \DB::commit();
    }
}
