<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\InventoryReport as InventoryReportModel;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointPurchasing\Models\Inventory\InvoiceItem as PurchaseInvoiceItem;

class InventoryReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:inventory-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'recalculate inventory report';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('recalculating inventory report');

        \DB::beginTransaction();

        // Get Latest Stock Opname
        $items = Item::all();
        
        $warehouseId = 1;

        $dateStart = "2021-09-30 23:59:59";
        $dateEnd = "2021-10-15 23:59:59";

        foreach ($items as $item) {
            // Check Latest Stock Opname
            $fs = StockOpname::join("formulir", "formulir.id", "=", "point_inventory_stock_opname.formulir_id")
                ->join("point_inventory_stock_opname_item", "point_inventory_stock_opname.id", "=", "point_inventory_stock_opname_item.stock_opname_id")
                ->where("point_inventory_stock_opname_item.item_id", $item->id)
                ->where("point_inventory_stock_opname.warehouse_id", $warehouseId)
                ->where("formulir.form_status", "1")
                ->where("formulir.form_number", "!=", "")
                ->where("formulir.form_date", "<=", $dateStart)
                ->orderBy('formulir.form_date', 'desc')
                ->select("point_inventory_stock_opname.*")
                ->addSelect("formulir.form_date as form_date")
                ->addSelect("formulir.form_number as form_number")
                ->first();

            $qtyStart = 0;
            $qtySum = 0;
            $qtySumIn = 0;
            $qtySumOut = 0;
            $qtyOpname = 0;
            $lastBuyPrice = 0;
            $lastBuyReference = "";
            
            if ($fs) {
                $stockOpnameReference = $fs->form_number;
                $fsi = StockOpnameItem::where("item_id", $item->id)->where("stock_opname_id", $fs->id)->first();
                $qtyOpname = $fsi->quantity_opname;
                $qtyStart = $qtyOpname;
                $qtySum = $qtyStart;
            }

            // List Inventory Before Start
            $inventories = Inventory::join("formulir", "formulir.id", "=", "inventory.formulir_id")
                ->orderBy('inventory.form_date', 'asc')
                ->where("formulir.formulirable_type", "!=", "Point\PointInventory\Models\StockOpname\StockOpname")
                ->where("inventory.warehouse_id", $warehouseId)
                ->where("inventory.item_id", $item->id)
                ->where("inventory.form_date", "<=", $dateEnd);
            if($fs) {
                $inventories = $inventories->where("inventory.form_date", ">", $fs->form_date);
            }
            $inventories = $inventories->get();

            foreach($inventories as $inventory) {
                if ($inventory->form_date <= $dateStart) {
                    // Sum Quantity Before Start
                    $qtyStart += $inventory->quantity;
                } else {
                    // Sum Quantity Start - End
                    if ($inventory->quantity > 0) {
                        $qtySumIn += $inventory->quantity;
                    } else {
                        $qtySumOut += $inventory->quantity;
                    }
                }
                $qtySum += $inventory->quantity;
            }


            $pi = PurchaseInvoiceItem::join("point_purchasing_invoice", "point_purchasing_invoice.id", "=", "point_purchasing_invoice_item.point_purchasing_invoice_id")
                ->join("formulir", "formulir.id", "=", "point_purchasing_invoice.formulir_id")
                ->where("item_id", $item->id)
                ->orderBy("formulir.form_date", "desc")
                ->select("point_purchasing_invoice_item.*")
                ->first();
            
            if ($pi) {
                $lastBuyPrice = $pi->price - $pi->discount;
                $lastBuyReference = $pi->invoice->formulir->form_number;
            }

            $ir = new InventoryReportModel();
            $ir->date_start = $dateStart;
            $ir->date_end = $dateEnd;
            $ir->item_code = $item->code;
            $ir->item_name = $item->name;
            $ir->quantity_opname = $qtyOpname;
            $ir->quantity_start = $qtyStart;
            $ir->quantity_in = $qtySumIn;
            $ir->quantity_out = $qtySumOut;
            $ir->quantity_end = $qtySum;
            if ($fs) {
                $ir->stock_opname_date = $fs->form_date;
                $ir->stock_opname_reference = $fs->form_number;
            }
            $ir->last_buy_reference = $lastBuyReference;
            $ir->last_buy_price = $lastBuyPrice;
            $ir->save();
        }

        \DB::commit();
    }
}
