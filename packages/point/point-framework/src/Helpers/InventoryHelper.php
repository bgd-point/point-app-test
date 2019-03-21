<?php

namespace Point\Framework\Helpers;

use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Framework\Models\Inventory;

class InventoryHelper
{
    private $inventory;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    public static function remove($formulir_id)
    {
        $inventories = Inventory::where('formulir_id', '=', $formulir_id)->get();

        foreach ($inventories as $inventory) {
            // recalculate if any inventory added before another inventory
            $inventory_next = Inventory::where('item_id', '=', $inventory->item_id)
                ->where('form_date', '>=', $inventory->form_date)
                ->where('warehouse_id', '=', $inventory->warehouse_id)
                ->first();
            if ($inventory_next) {
                $inventory_next->recalculate = true;
                $inventory_next->save();
            }

            $inventory->delete();
        }
    }

    public static function getClosingStock($date_from, $date_to, $item_id, $warehouse_id)
    {
        return static::getOpeningStock($date_from, $item_id, $warehouse_id)
        + static::getStockIn($date_from, $date_to, $item_id, $warehouse_id)
        + static::getStockOut($date_from, $date_to, $item_id, $warehouse_id);
    }

    public static function getOpeningStock($date_from, $item_id, $warehouse_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '<', $date_from)
            ->where('warehouse_id', '=', $warehouse_id)
            ->sum('quantity');
    }

    public static function getStockIn($date_from, $date_to, $item_id, $warehouse_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->where('quantity', '>=', 0)
            ->where('warehouse_id', '=', $warehouse_id)
            ->sum('quantity');
    }

    public static function getStockOut($date_from, $date_to, $item_id, $warehouse_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->where('quantity', '<', 0)
            ->where('warehouse_id', '=', $warehouse_id)
            ->sum('quantity');
    }

    public static function getClosingValue($date_from, $date_to, $item_id, $warehouse_id)
    {
        return static::getOpeningValue($date_from, $item_id, $warehouse_id)
            + static::getValueIn($date_from, $date_to, $item_id, $warehouse_id)
            + static::getValueOut($date_from, $date_to, $item_id, $warehouse_id);
    }

    public static function getOpeningValue($date_from, $item_id, $warehouse_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '<', $date_from)
            ->where('warehouse_id', '=', $warehouse_id)
            ->select(DB::raw('SUM(quantity * price) as value'))->first()->value;
    }

    public static function getValueIn($date_from, $date_to, $item_id, $warehouse_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->where('quantity', '>=', 0)
            ->where('warehouse_id', '=', $warehouse_id)
            ->select(DB::raw('SUM(quantity * price) as value'))->first()->value;
    }

    public static function getValueOut($date_from, $date_to, $item_id, $warehouse_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->where('quantity', '<', 0)
            ->where('warehouse_id', '=', $warehouse_id)
            ->select(DB::raw('SUM(quantity * price) as value'))->first()->value;
    }

    public static function getClosingStockAll($date_from, $date_to, $item_id)
    {
        return static::getOpeningStockAll($date_from, $item_id)
        + static::getStockInAll($date_from, $date_to, $item_id)
        + static::getStockOutAll($date_from, $date_to, $item_id);
    }

    public static function getOpeningStockAll($date_from, $item_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '<', $date_from)
            ->sum('quantity');
    }

    public static function getStockInAll($date_from, $date_to, $item_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->where('quantity', '>=', 0)
            ->sum('quantity');
    }

    public static function getStockOutAll($date_from, $date_to, $item_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->where('quantity', '<', 0)
            ->sum('quantity');
    }

    public static function getClosingValueAll($date_from, $date_to, $item_id)
    {
        return static::getOpeningValueAll($date_from, $item_id)
            + static::getValueInAll($date_from, $date_to, $item_id)
            + static::getValueOutAll($date_from, $date_to, $item_id);
    }

    public static function getOpeningValueAll($date_from, $item_id)
    {
        $inventory = Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '<', $date_from)
            ->orderBy('form_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($inventory) {
            return $inventory->total_value;
        }

        return 0;
    }

    public static function getValueInAll($date_from, $date_to, $item_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->where('quantity', '>=', 0)
            ->select(DB::raw('SUM(quantity * cogs) as value'))->first()->value;
    }

    public static function getValueOutAll($date_from, $date_to, $item_id)
    {
        return Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->where('quantity', '<', 0)
            ->select(DB::raw('SUM(quantity * cogs) as value'))->first()->value;
    }

    /**
     * Get last stock of item in warehouse in choosen date
     *
     * @param $date
     * @param $item_id
     * @param $warehouse_id
     *
     * @return int
     */
    public static function getAvailableStock($date, $item_id, $warehouse_id)
    {
        $inventory = Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '<=', $date)
            ->where('warehouse_id', '=', $warehouse_id)
            ->orderBy('form_date', 'desc')
            ->orderBy('formulir_id', 'desc')
            ->first();

        if (!$inventory) {
            return 0;
        }

        return $inventory->total_quantity;
    }

    /**
     * Get average cost of sales from specific warehouse
     *
     * @param $date
     * @param $item_id
     * @param $warehouse_id
     *
     * @return int
     */
    public static function getCostOfSales($date, $item_id, $warehouse_id)
    {
        $inventory = Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '<=', $date)
            ->where('warehouse_id', '=', $warehouse_id)
            ->orderBy('form_date', 'desc')
            ->first();

        if (!$inventory) {
            return 0;
        }

        return $inventory->cogs;
    }

    /**
     * Get average cost of sales from all warehouse
     *
     * @param $date
     * @param $item_id
     *
     * @return float|int
     */
    public static function getAverageCostOfSales($date, $item_id)
    {
        $inventories = Inventory::where('item_id', '=', $item_id)
            ->where('form_date', '<=', $date)
            // ->groupBy('warehouse_id')
            ->orderBy('form_date', 'desc')
            // ->get();
            ->first();

        if ($inventories) {
            $average_cost_of_sales = $inventories['cogs'];
        } else {
            $average_cost_of_sales = 0;
        }
        // $average_cost_of_sales = 0;
        // $count_warehouse = 0;

        // foreach ($inventories as $inventory) {
        //     $average_cost_of_sales += $inventory->cogs;
        //     $count_warehouse++;
        // }

        // if ($average_cost_of_sales) {
        //     $average_cost_of_sales = $average_cost_of_sales / $count_warehouse;
        // }

        return $average_cost_of_sales;
    }

    public static function getAllAvailableStock($date, $warehouse_id)
    {
        return $inventory = Inventory::join('item', 'inventory.item_id', '=', 'item.id')
            ->where('form_date', '<=', $date)
            ->where('warehouse_id', '=', $warehouse_id)
            ->select('inventory.*', DB::raw('CONCAT("[", item.code, "] ", item.name) AS codeName'))
            ->groupBy('item_id')
            ->orderBy('item.code')
            ->get();
    }

    public static function getItem($date, $warehouse_id)
    {
        return $inventory = Inventory::join('item', 'inventory.item_id', '=', 'item.id')
            ->where('form_date', '<=', $date)
            ->where('warehouse_id', '=', $warehouse_id)
            ->select('item.id as value', DB::raw('CONCAT("[", item.code, "] ", item.name) AS text'), 'item.code as code')
            ->groupBy('item_id')
            ->orderBy('item.code')
            ->get()
            ->toArray();
    }

    /**
     * Increase stock to inventory
     *
     * @throws \Point\Core\Exceptions\PointException
     */
    public function in()
    {
        // doesn't allow minus quantity to use this method
        if ($this->inventory->quantity < 0) {
            throw new PointException('Inventory error, please contact our support');
        }

        // update cogs
        $this->updateCogsIn();

        // mark recalculate if any inventory added before another inventory
        $this->markRecalculate();

        // save inventory
        $this->inventory->save();
    }

    /**
     * Decrease stock from inventory
     *
     * @throws \Point\Core\Exceptions\PointException
     */
    public function out()
    {
        // doesn't allow minus quantity to use this method
        if ($this->inventory->quantity < 0) {
            throw new PointException('Inventory error, please contact our support');
        }

        // setup quantity to minus in database
        $this->inventory->quantity *= -1;

        // update cogs
        $this->updateCogsOut();

        // mark recalculate if any inventory added before another inventory
        $this->markRecalculate();

        // save inventory
        $this->inventory->save();
    }

    private function updateCogsIn()
    {
        $last = Inventory::where('item_id', '=', $this->inventory->item_id)
            ->where('form_date', '<=', $this->inventory->form_date)
            ->where('warehouse_id', '=', $this->inventory->warehouse_id)
            ->orderBy('form_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $this->inventory->total_quantity = $last->total_quantity + $this->inventory->quantity;
            $this->inventory->total_value = $last->total_value + ($this->inventory->quantity * $this->inventory->price);
        } else {
            $this->inventory->total_quantity = $this->inventory->quantity;
            $this->inventory->total_value = $this->inventory->quantity * $this->inventory->price;
        }

        // handle error division by zero
        if ($this->inventory->total_quantity == 0) {
            $this->inventory->cogs = 0;
        } else {
            $this->inventory->cogs = $this->inventory->total_value / $this->inventory->total_quantity;
        }
    }

    private function updateCogsOut()
    {
        $last = Inventory::where('item_id', '=', $this->inventory->item_id)
            ->where('form_date', '<=', $this->inventory->form_date)
            ->where('warehouse_id', '=', $this->inventory->warehouse_id)
            ->orderBy('form_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$last) {
            throw new PointException('STOCK ' . $this->inventory->item->name . ' NOT AVAILABLE');
        }

        $this->inventory->total_quantity = $last->total_quantity + $this->inventory->quantity;
        $this->inventory->total_value = $last->total_value + ($this->inventory->quantity * $last->cogs);

        $this->inventory->cogs = $last->cogs;
    }

    private function markRecalculate()
    {
        if (Inventory::where('item_id', '=', $this->inventory->item_id)
                ->where('form_date', '>=', $this->inventory->form_date)
                ->where('warehouse_id', '=', $this->inventory->warehouse_id)
                ->get()
                ->count() > 0
        ) {
            $this->inventory->recalculate = true;
        }
    }
}
