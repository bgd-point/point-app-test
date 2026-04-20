<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\Retur;

class RecalculateDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:date';

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

        $transferItems = TransferItem::join('formulir', 'formulir.id', '=', 'point_inventory_transfer_item.formulir_id')
            ->select('point_inventory_transfer_item.*')
            ->get();            
        foreach($transferItems as $transferItem) {
            if ($transferItem->received_date) {
                $transferItem->received_date = $transferItem->formulir->updated_at;
                $transferItem->save();

                $journals = Journal::where('form_journal_id', '=', $transferItem->formulir->id)
                    ->where('description', 'like', 'receive item%')
                    ->get();
                
                foreach($journals as $journal) {
                    $journal->form_date = $transferItem->received_date;
                    $journal->save();
                }       
            }
        }

        $list_sales = Invoice::join('formulir', 'formulir.id', '=', 'point_sales_invoice.formulir_id')
            ->select('point_sales_invoice.*')
            ->get();

        foreach ($list_sales as $sales) {
            $this->comment($sales->formulir_id);
            $journals = Journal::where('form_journal_id', '=', $sales->formulir->id)
                ->get();
            
            foreach($journals as $journal) {
                $journal->form_date = $sales->formulir->approval_at;
                $journal->save();
            }
        }

        $returs = Retur::join('formulir', 'formulir.id', '=', 'point_sales_retur.formulir_id')
            ->select('point_sales_retur.*')
            ->get();            
        foreach($returs as $retur) {
            $journals = Journal::where('form_journal_id', '=', $retur->formulir->id)
                ->get();
            
            foreach($journals as $journal) {
                $journal->form_date = $retur->received_date;
                $journal->save();
            }       
        }

        \DB::commit();
    }
}