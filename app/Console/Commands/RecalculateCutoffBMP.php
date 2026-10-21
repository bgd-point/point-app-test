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

class RecalculateCutoffBMP extends Command
{
  /**
   * The name and signature of the console command.
   * 
   * dev:recalculate:cutoff
   * dev:recalculate:allval
   * dev:recalculate:all
   * dev:recalculate:jhppkb
   *
   * @var string
   */
  protected $signature = 'dev:recalculate:cutoff-bmp';

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

    $items = Inventory::join('item', 'item.id', '=', 'inventory.item_id')->groupBy('item_id')->select('item.*')->get();

    foreach ($items as $item) {
      $inventories = Inventory::orderBy('form_date', 'desc')
        ->orderBy('formulir_id', 'desc')
        ->orderBy('id', 'desc')
        ->where('item_id', '=', $item->id)
        ->get()
        ->unique(function ($inventory) {
          return $inventory['item_id'] . $inventory['warehouse_id'];
        });

      foreach ($inventories as $inventory) {
        $last = Inventory::where('item_id', '=', $inventory->item_id)
          ->where('form_date', '<', '2026-06-30 23:59:00')
          ->where('warehouse_id', '=', $inventory->warehouse_id)
          ->orderBy('form_date', 'desc')
          ->orderBy('formulir_id', 'desc')
          ->orderBy('id', 'desc')
          ->first();

        $lastVal = Inventory::where('item_id', '=', $inventory->item_id)
          ->where('form_date', '<', '2026-06-30 23:59:00')
          ->orderBy('form_date', 'desc')
          ->orderBy('formulir_id', 'desc')
          ->orderBy('id', 'desc')
          ->first();

        if (!$last) {
          continue;
        }

        if ($last->total_quantity == 0) {
          continue;
        }

        // TODO: Delete all item from warehouse to, so cogs, total quantity, total value is reset to 0
        $form_date = '2026-06-30 23:59:00';
        $form_number = FormulirHelper::number('point-inventory-stock-correction', $form_date);

        $formulir = new Formulir;
        $formulir->form_date = $form_date;
        $formulir->created_at = $form_date;
        $formulir->updated_at = $form_date;
        $formulir->form_number = $form_number['form_number'];
        $formulir->form_raw_number = $form_number['raw'];
        $formulir->notes = 'Cutoff Stock 2026-06-30';
        $formulir->approval_to = 1;
        $formulir->approval_status = 1;
        $formulir->approval_message = '';
        $formulir->created_by = 1;
        $formulir->updated_by = 1;
        if (!$formulir->save()) {
          gritter_error('create has been failed', false);
        }

        $stock_correction = new StockCorrection;
        $stock_correction->formulir_id = $formulir->id;
        $stock_correction->warehouse_id = $inventory->warehouse_id;
        $stock_correction->save();

        $stock_correction_item = new StockCorrectionItem;
        $stock_correction_item->point_inventory_stock_correction_id = $stock_correction->id;
        $stock_correction_item->item_id = $item->id;
        $stock_correction_item->stock_in_database = $last->total_quantity;
        $stock_correction_item->quantity_correction = $last->total_quantity * -1;
        $stock_correction_item->correction_notes = 'Cutoff Stock 2026-06-30';
        $unit = $stock_correction_item->item->unit()->first();
        $stock_correction_item->unit = $unit->name;
        $stock_correction_item->converter = $unit->converter;
        $stock_correction_item->save();

        $inventory = new Inventory;
        $inventory->form_date = '2026-06-30 23:59:00';
        $inventory->formulir_id = $stock_correction->formulir_id;
        $inventory->warehouse_id = $stock_correction->warehouse_id;
        $inventory->item_id = $stock_correction_item->item_id;
        $inventory->quantity = $stock_correction_item->quantity_correction;
        $inventory->price = $lastVal->cogs;
        if ($inventory->quantity < 0) {
          $inventory->quantity *= -1;
          $inventory_helper = new InventoryHelper($inventory);
          $inventory_helper->out0();
        } else {
          $inventory_helper = new InventoryHelper($inventory);
          $inventory_helper->in();
        }
      }
    }

    \DB::commit();
  }
}
