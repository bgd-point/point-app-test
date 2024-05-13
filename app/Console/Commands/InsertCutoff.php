<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\PointAccounting\Models\CutOffInventoryDetail;

class RecalculateBBL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:insert-cutoff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'insert cutoff';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('insert cutoff');

        \DB::beginTransaction();

        // Get all items
        $cinventories = CutOffInventoryDetail::all();

        foreach ($cinventories as $cinventory) {
            $inventory = new Inventory;
            $inventory->form_date = "2024-05-08 00:00:00";
            $inventory->formulir_id = 17;
            $inventory->warehouse_id = $cinventory->warehouse_id;
            $inventory->item_id = $cinventory->subledger_id;
            
            if ($cinventory->amount == 0) {
                $inventory->price =  0;
                $inventory->cogs =  0;
            } else {
                $inventory->price =  $cinventory->amount / $cinventory->stock;
                $inventory->cogs =  $cinventory->amount / $cinventory->stock;
            }
            $inventory->quantity = $cinventory->stock;
            $inventory->total_quantity = $cinventory->stock;
            $inventory->total_value = $cinventory->amount;
            $inventory->save();
        }
        
        \DB::commit();
    }
}