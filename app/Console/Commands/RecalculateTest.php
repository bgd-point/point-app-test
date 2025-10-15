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

        $inventories = Inventory::orderBy('form_date', 'asc')
            ->get()
            ->unique(function ($inventory) {
                return $inventory['item_id'].$inventory['warehouse_id'];
            });

        foreach ($inventories as $inventory) {

            $list_inventory = Inventory::with('formulir')
                ->where('inventory.item_id', $inventory->item_id)
                ->where('inventory.warehouse_id', $inventory->warehouse_id)
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            $prevCogs = 0;
            $prevTotalQty = 0;
            $prevTotalValue = 0;

            foreach($list_inventory as $index => $l_inventory) {
                $l_inventory->recalculate = 0;

                $this->comment($l_inventory->item_id . ' : ' . $l_inventory->warehouse_id);
    
                // Handle the very first transaction to establish the baseline
                if ($index == 0) {
                    $l_inventory->total_quantity = (float) $l_inventory->quantity;
                    $l_inventory->total_value = (float) $l_inventory->quantity * (float) $l_inventory->price;
                    // COGS is calculated based on the initial total value / total quantity
                    if ((float) $l_inventory->total_quantity === 0) {
                        $l_inventory->cogs = 0;
                    } else {
                        $l_inventory->cogs = (float) $l_inventory->total_value / (float) $l_inventory->total_quantity;
                    }
    
                // Handle Stock Opname transactions
                } else if ($l_inventory->formulir->formulirable_type === StockOpname::class) {
                    // If SO quantity is negative, it's costed at the previous average cost
                    if ((float) $l_inventory->quantity < 0) {
                        $l_inventory->price = $prevCogs;
                        $l_inventory->cogs = $prevCogs;
                    }
                    // NOTE: The total_quantity is not updated here, which may be a bug in the original logic.
                    // NOTE: The total_value calculation is flawed as it uses $l_inventory->total_quantity before updating it.
                    $l_inventory->total_value = $l_inventory->cogs * $l_inventory->total_quantity;
    
                // Handle Sales Return transactions (output)
                } else if ($l_inventory->formulir->formulirable_type === Retur::class) {
                    // Output transactions (like Retur) are costed at the previous average cost
                    $l_inventory->price = $prevCogs;
                    $l_inventory->cogs = $prevCogs;
    
                // Handle all other transactions (Inputs and regular Outputs)
                } else {
                    // For output transactions (quantity < 0), use the previous average cost (MAC)
                    if ((float) $l_inventory->quantity < 0) {
                        $l_inventory->price = $prevCogs;
                        $l_inventory->cogs = $prevCogs;
                    }
    
                    // Calculate the new running total quantity
                    $l_inventory->total_quantity = (float) $l_inventory->quantity + $prevTotalQty;
    
                    if ($l_inventory->total_quantity > 0) {
                        // Calculate the new running total value (Transaction Value + Previous Total Value)
                        $l_inventory->total_value = (float) $l_inventory->quantity * (float) $l_inventory->price + $prevTotalValue;
                        
                        // Calculate the new Moving Average Cost (MAC)
                        $l_inventory->cogs = (float) $l_inventory->total_value / (float) $l_inventory->total_quantity;
                    } else {
                        $l_inventory->total_value = 0;
                        $l_inventory->cogs = 0;
                    }
                }
    
                // Update the rolling tracking variables for the next iteration
                $prevCogs = (float) $l_inventory->cogs;
                $prevTotalQty = (float) $l_inventory->total_quantity;
                $prevTotalValue = (float) $l_inventory->total_value;
    
                // Final check to ensure value is zero if stock is zero or less
                if ((float) $l_inventory->total_quantity <= 0) {
                    $l_inventory->total_value = 0;
                }
    
                // Flag for potential attention if stock goes negative
                if ((float) $l_inventory->total_quantity < 0) {
                    $l_inventory->recalculate = 1;
                }
    
                $l_inventory->save();
    
                // $this->comment($l_inventory->form_date . ' Q= ' . $l_inventory->quantity. ' P= ' . $l_inventory->price . ' C= ' . $l_inventory->cogs . ' TQ= ' . $l_inventory->total_quantity . ' TV= ' . $l_inventory->total_value);
            }
        }

        \DB::commit(); 
    }
}