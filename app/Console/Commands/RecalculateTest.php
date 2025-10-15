<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointSales\Models\Sales\Retur;

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
    protected $description = 'recalculate inventory using moving average cost (MAC) method';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Starting inventory recalculation using MAC...');

        \DB::beginTransaction();

        try {
            // Retrieve inventory records, ordered chronologically
            $list_inventory = Inventory::with('formulir')
                ->where('inventory.item_id', 102)
                ->where('inventory.warehouse_id', 1)
                ->where('inventory.form_date', '>=', '2025-08-01')
                ->where('inventory.form_date', '<', '2025-09-01')
                ->orderBy('form_date', 'asc')
                ->orderBy('formulir_id', 'asc')
                ->get();

            // Initialize tracking variables
            $currentTotalQuantity = 0; // The rolling total quantity in stock
            $currentTotalValue = 0;    // The rolling total value (cost) of stock
            $currentCogs = 0;          // The calculated Moving Average Cost

            // Loop through all inventory records
            foreach ($list_inventory as $l_inventory) {
                $quantity = (float) $l_inventory->quantity;
                $price = (float) $l_inventory->price;
                $isStockOpname = $l_inventory->formulir->formulirable_type === StockOpname::class;
                $isOutput = $quantity < 0 || $l_inventory->formulir->formulirable_type === Retur::class;

                // --- 1. Calculate New Total Quantity ---
                // Total quantity is always the previous total plus the current transaction's quantity
                $newTotalQuantity = $currentTotalQuantity + $quantity;

                if ($isOutput) {
                    // --- 2. Handle Output/Negative Quantities (Sales, Returns Out, etc.) ---
                    // For outputs, the cost is the current Moving Average Cost (MAC)
                    $l_inventory->price = $currentCogs;
                    $l_inventory->cogs = $currentCogs; // COGS for the transaction
                    
                    // The value of the transaction is Quantity * Current MAC
                    $transactionValue = $quantity * $currentCogs;

                    // New Total Value is Previous Total Value + Transaction Value (since quantity is negative, this subtracts)
                    $newTotalValue = $currentTotalValue + $transactionValue;
                    
                    // The MAC doesn't change for output transactions unless the remaining stock is zero/negative
                    $newCogs = $currentCogs;

                } elseif ($isStockOpname) {
                    // --- 3. Handle Stock Opname (SO) ---
                    // SO represents the physical count. It resets the stock state.
                    // Assuming SO is recorded as a single IN entry reflecting the count difference.

                    // If it's an SO, we take the price recorded on the SO. If zero, use current MAC.
                    $l_inventory->price = $price ?: $currentCogs; 
                    
                    // Value of the SO transaction
                    $transactionValue = $quantity * $l_inventory->price;
                    
                    // New Total Value = Previous Total Value + Transaction Value
                    $newTotalValue = $currentTotalValue + $transactionValue;

                    // Recalculate MAC after the SO adjustment if the new total quantity is positive
                    $newCogs = $newTotalQuantity > 0 ? $newTotalValue / $newTotalQuantity : 0;
                    $l_inventory->cogs = $newCogs;

                } else {
                    // --- 4. Handle Input/Positive Quantities (Purchases, Returns In, etc.) ---
                    
                    // For inputs, the transaction value is Quantity * Price (from the record)
                    $transactionValue = $quantity * $price;
                    
                    // New Total Value is Previous Total Value + Transaction Value
                    $newTotalValue = $currentTotalValue + $transactionValue;

                    // Recalculate MAC: (Total Value after transaction) / (Total Quantity after transaction)
                    $newCogs = $newTotalQuantity > 0 ? $newTotalValue / $newTotalQuantity : 0;
                    $l_inventory->cogs = $newCogs;
                    
                    // If the original price was 0, it should be updated to the new MAC for consistency.
                    if ($price == 0) {
                        $l_inventory->price = $newCogs;
                    }
                }

                // --- 5. Finalizing and Saving the Inventory Record ---
                
                // Ensure total value is 0 if total quantity is 0 or less
                if ($newTotalQuantity <= 0) {
                    $newTotalValue = 0;
                    $newCogs = 0;
                }

                $l_inventory->total_quantity = $newTotalQuantity;
                $l_inventory->total_value = $newTotalValue;
                $l_inventory->recalculate = 0; // Set to 0 after recalculation

                // Save the changes to the database record
                $l_inventory->save();

                // Update tracking variables for the next iteration
                $currentTotalQuantity = $newTotalQuantity;
                $currentTotalValue = $newTotalValue;
                $currentCogs = $newCogs;

                $this->comment(
                    $l_inventory->form_date 
                    . ' | Qty: ' . $quantity 
                    . ' | Price: ' . number_format($l_inventory->price, 4)
                    . ' | New Total Qty: ' . $currentTotalQuantity
                    . ' | New Total Val: ' . number_format($currentTotalValue, 2) 
                    . ' | New COGS: ' . number_format($currentCogs, 4)
                );
            }

            // \DB::commit();
            $this->info('Inventory recalculation complete!');

        } catch (\Exception $e) {
            \DB::rollback();
            $this->error('An error occurred during recalculation: ' . $e->getMessage());
        }
    }
}