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
class RecalculateJournalHpp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:journal-hpp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'recalculate journal hpp';

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

        $inventories = Inventory::orderBy('form_date', 'asc')
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'];
            });

        foreach ($inventories as $inventory) {

            $list_inventory = Inventory::with('formulir')
                ->where('inventory.item_id', $inventory->item_id)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            $this->comment('INVENTORY ' . $inventory->item_id);

            foreach($list_inventory as $index => $l_inventory) {
                $journals = Journal::join('coa', 'coa.id', '=', 'journal.coa_id')
                    ->where('journal.form_journal_id', '=', $l_inventory->formulir_id)
                    ->where('journal.subledger_id', '=', $l_inventory->item_id)
                    ->where('journal.subledger_type', '=', "Point\Framework\Models\Master\Item")
                    ->select('journal.*')
                    ->get();

                foreach($journals as $journal) {
                    $jValue = round(abs($journal->debit + $journal->credit),4);
                    $iValue = round(abs($l_inventory->quantity * $l_inventory->price),4);
                    if ($jValue !== $iValue) {
                        $this->comment($journal->id . ' = ' . $iValue . ' != ' . $jValue . ' = ' . $journal->coa->coa_number . ' = ' . $journal->coa->name);

                        if ($journal->debit > 0) {
                            $this->comment($journal->id . ' = ' . $iValue . ' (DEBIT FIXED) ');
                            $journal->debit = $iValue;

                            $j = Journal::where('form_journal_id', '=', $journal->form_journal_id)
                                ->where('coa_id', '=', 385)
                                ->get();

                            $this->comment($journal->form_journal_id . ' = ' . count($j));
                        } else {
                            $this->comment($journal->id . ' = ' . $iValue . ' (CREDIT FIXED) ');
                            $journal->credit = $iValue;

                            $j = Journal::where('form_journal_id', '=', $journal->form_journal_id)
                                ->where('coa_id', '=', 385)
                                ->get();

                            $this->comment($journal->form_journal_id . ' = ' . count($j));
                        }
                        // $journal->save();
                    }
                }
            }
        }

        \DB::commit(); 
    }
}