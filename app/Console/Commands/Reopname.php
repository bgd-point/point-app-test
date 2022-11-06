<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Item;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;

class Reopname extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:reopname';

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

        $date = '2022-07-01';

        $this->clearOpanme($date);
        $this->updatePendingOpname($date);
        $this->repostingOpname($date);

        \DB::commit();
    }

    private function clearOpanme($date) {
        $opnames = StockOpname::join('formulir', 'formulir.id', '=', 'point_inventory_stock_opname.formulir_id')
            ->where('formulir.form_date', '>=', $date)
            ->whereNotNull('formulir.form_number')
            ->orderBy('formulir.form_date', 'asc')
            ->select('point_inventory_stock_opname.*')
            ->get();

        foreach($opnames as $opname) {
            Inventory::where('formulir_id', $opname->formulir_id)->delete();
            Journal::where('form_journal_id', $opname->formulir_id)->delete();
        }
    }

    private function updatePendingOpname() {
        $opnames = StockOpname::join('formulir', 'formulir.id', '=', 'point_inventory_stock_opname.formulir_id')
            ->where('formulir.form_date', '>=', $date)
            ->where('formulir.form_status', '=', 0)
            ->whereNotNull('formulir.form_number')
            ->orderBy('formulir.form_date', 'asc')
            ->select('point_inventory_stock_opname.*')
            ->get();

        foreach($opnames as $opname) {
            foreach ($opname->items as $opnameItem) {
                $inv = Inventory::where('inventory.item_id', $opnameItem->item_id)
                    ->where('form_date', '<=', $opname->formulir->form_date)
                    ->where('warehouse_id', $opname->warehouse_id)
                    ->orderBy('form_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->sum('quantity');

                $opnameItem->stock_in_database = $inv ? $inv : 0;
                $opnameItem->save();
            }
        }
    }

    private function repostingOpname($date) {
        $opnames = StockOpname::join('formulir', 'formulir.id', '=', 'point_inventory_stock_opname.formulir_id')
            ->where('formulir.form_date', '>=', $date)
            ->where('formulir.form_status', '>=', 0)
            ->where('formulir.approval_status', '>', 0)
            ->whereNotNull('formulir.form_number')
            ->orderBy('formulir.form_date', 'asc')
            ->select('point_inventory_stock_opname.*')
            ->get();

        foreach($opnames as $opname) {
            foreach ($opname->items as $opnameItem) {
                $inv = Inventory::where('inventory.item_id', $opnameItem->item_id)
                    ->where('form_date', '<=', $opname->formulir->form_date)
                    ->where('warehouse_id', $opname->warehouse_id)
                    ->orderBy('form_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->sum('quantity');

                $opnameItem->stock_in_database = $inv ? $inv : 0;
                $opnameItem->save();
            }

            Inventory::where('formulir_id', $opname->formulir_id)->delete();

            foreach ($opname->items as $opnameItem) {
                $inventory = new Inventory;
                $inventory->form_date = $opname->formulir->form_date;
                $inventory->formulir_id = $opname->formulir_id;
                $inventory->warehouse_id = $opname->warehouse_id;
                $inventory->item_id = $opnameItem->item_id;
                $inventory->price =  InventoryHelper::getCostOfSales($opname->formulir->form_date, $opnameItem->item_id, $opname->warehouse_id);
                $quantity = $opnameItem->quantity_opname - $opnameItem->stock_in_database;
                if ($quantity < 0) {
                    $inventory->quantity = $quantity * -1;
                    $inventory_helper = new InventoryHelper($inventory);
                    $inventory_helper->out();
                } elseif ($quantity > 0) {
                    $inventory->quantity = $quantity;
                    $inventory_helper = new InventoryHelper($inventory);
                    $inventory_helper->in();
                } else {
                    continue;
                }
            }
        }
    }
}
