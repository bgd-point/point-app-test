<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;
use Point\PointPurchasing\Models\Inventory\Invoice;

class Recalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate';

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
        $this->comment('recalculating inventory');
        \DB::beginTransaction();

        // get invoices date and its receive date
        $invoices = \DB::table('point_purchasing_invoice AS ppi')
            ->join('formulir_lock AS fl', 'fl.locking_id', '=', 'ppi.formulir_id')
            ->join('point_purchasing_goods_received AS ppgr', 'ppgr.formulir_id', '=', 'fl.locked_id')
            ->join('formulir AS f1', 'f1.id', '=', 'fl.locking_id')
            ->join('formulir AS f2', 'f2.id', '=', 'fl.locked_id')
            ->select(
                'f1.id AS invoice_formulir_id',
                'f2.id AS receive_formulir_id',
                'f1.form_date AS invoice_date',
                'f2.form_date AS received_date'
            )
            ->get();

        // update invoice date to have same date as goods receive
        foreach($invoices as $invoice) {
            \DB::table('formulir')
                ->where('id', $invoice->invoice_formulir_id)
                ->update(['form_date' => $invoice->received_date]);
            \DB::table('inventory')
                ->where('formulir_id', $invoice->invoice_formulir_id)
                ->update(['form_date' => $invoice->received_date]);
        }

        // get list of unique item_id in inventory
        $inventories = Inventory::select('item_id')->distinct()->get();

        foreach ($inventories as $inventory) {
            $item_activity = Inventory::where('item_id', '=', $inventory->item_id)->orderBy('form_date')->get();

            $total_quantity = 0;
            $total_value = 0;
            $cogs = 0;

            foreach ($item_activity as $l_inventory) {
                // UPDATE TOTAL QUANTITY
                if ($l_inventory->quantity > 0) {
                    // STOCK IN
                    $l_inventory->form_date = date('Y-m-d 00:00:00', strtotime($l_inventory->form_date));

                    if ($total_quantity + $l_inventory->quantity > 0) {
                        $cogs = ($total_value + $l_inventory->quantity * $l_inventory->price) / ($total_quantity + $l_inventory->quantity) ;
                    }
                }
                else {
                    // STOCK OUT
                    $l_inventory->form_date = date('Y-m-d 23:59:59', strtotime($l_inventory->form_date));
                }

                $total_quantity += $l_inventory->quantity;
                $total_value = $cogs * $total_quantity;

                $l_inventory->recalculate = $total_quantity < 0;
                $l_inventory->cogs = $cogs;
                $l_inventory->total_quantity = $total_quantity;
                $l_inventory->total_value = $total_value;
                $l_inventory->save();
            }
        }

        \DB::commit();
    }
}
