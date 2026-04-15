<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Retur;
use Point\Framework\Models\Journal;

/**
 * Class RecalculateTest
 *
 * This command is designed to recalculate inventory valuation for a specific
 * item and warehouse. It implements a **perpetual inventory** system,
 * likely following a **Moving Average Cost (MAC)** method, by iterating
 * through inventory records chronologically and updating the rolling
 * total quantity, total value, and average cost (cogs).
 */
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
    protected $description = 'recalculate inventory';

    /**
     * Execute the console command.
     *
     * This method fetches inventory records for item 102 and warehouse 1,
     * sorted by date, and processes them sequentially to recalculate
     * the total quantity, total value, and cost of goods sold (cogs)
     * using the Moving Average Cost (MAC) logic.
     *
     * The logic relies on maintaining rolling totals: $prevCogs, $prevTotalQty, and $prevTotalValue.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('recalculating inventory');

        \DB::beginTransaction();

        $journals = Journal::join('coa', 'coa.id', '=', 'journal.coa_id')
            ->join('formulir', 'formulir.id', '=', 'journal.form_journal_id')
            ->where('formulir.formulirable_type', '=', 'Point\PointManufacture\Models\OutputProcess')
            ->where('journal.debit', '>', 0)
            ->select('journal.*')
            ->get();

        foreach($journals as $journal) {
            $inventory = Inventory::where('formulir_id', '=', $journal->form_journal_id)
                ->where('item_id', '=', $journal->subledger_id)
                ->first();

            $inventory->price = $journal->debit / $inventory->quantity;
            // $inventory->save();
            $this->comment($journal->id . ' = ' . $journal->formulir_id . ' = ' . $inventory->price);
        }

        \DB::commit(); 
    }
}