<?php

namespace Point\BumiShares\Helpers;

use Point\BumiShares\Models\Stock;
use Point\BumiShares\Models\StockFifo;
use Point\Core\Exceptions\PointException;

class SharesStockHelper
{
    public static function in($shares_buy)
    {
        if ($shares_buy->quantity <= 0) {
            return false;
        }

        $subtotal = $shares_buy->quantity * $shares_buy->price; // before broker commission
        $total = $subtotal + ($subtotal * $shares_buy->fee / 100); // after broker commission

        // check last item in stock
        $last_stock = self::getLastStock($shares_buy);

        // update average_price
        $acc_quantity = $shares_buy->quantity;
        $acc_subtotal = $subtotal;
        $acc_total = $total;

        if ($last_stock) {
            $acc_quantity = $last_stock->acc_quantity + ($shares_buy->quantity);
            $acc_subtotal = $last_stock->acc_subtotal + $subtotal;
            $acc_total = $last_stock->acc_total + $total;
        }

        // insert new item in stock
        $stock = new Stock;
        $stock->formulir_id = $shares_buy->formulir_id;
        $stock->date = $shares_buy->formulir->form_date;
        $stock->broker_id = $shares_buy->broker_id;
        $stock->shares_id = $shares_buy->shares_id;
        $stock->owner_id = $shares_buy->owner_id;
        $stock->owner_group_id = $shares_buy->owner_group_id;
        $stock->quantity = $shares_buy->quantity;
        $stock->remaining_quantity = $shares_buy->quantity;
        $stock->price = $shares_buy->price;
        $stock->subtotal = $subtotal;
        $stock->fee = $shares_buy->fee;
        $stock->total = $total;
        $stock->acc_quantity = $acc_quantity;
        $stock->acc_subtotal = $acc_subtotal;
        $stock->acc_total = $acc_total;
        $stock->average_price = $shares_buy->price;
        $stock->recalculate = self::recalculate($shares_buy);

        $stock->save();
    }

    public static function out($shares_sell)
    {
        if ($shares_sell->quantity <= 0) {
            throw new PointException('Selected Date Error');
        }

        // check last item in stock
        $list_stock = Stock::where('shares_id', '=', $shares_sell->shares_id)
            ->where('owner_group_id', '=', $shares_sell->owner_group_id)
            ->where('owner_id', '=', $shares_sell->owner_id)
            ->where('broker_id', '=', $shares_sell->broker_id)
            ->where('date', '<', $shares_sell->formulir->form_date)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $selling_quantity = $shares_sell->quantity;

        foreach ($list_stock as $stock) {
            if ($selling_quantity >= 0) {
                $stock_fifo = new StockFifo;
                if ($selling_quantity >= $stock->remaining_quantity) {
                    $selling_quantity -= $stock->remaining_quantity;
                    $stock_fifo->quantity = $stock->remaining_quantity;
                    $stock->remaining_quantity = 0;
                } else {
                    $stock->remaining_quantity -= $selling_quantity;
                    $stock_fifo->quantity = $selling_quantity;
                    $selling_quantity = 0;
                }
                $stock_fifo->shares_in_id = $stock->formulir_id;
                $stock_fifo->shares_out_id = $shares_sell->formulir_id;
                $stock_fifo->average_price = $stock->average_price;
                $stock_fifo->price = $shares_sell->price;
                $stock_fifo->save();
                $stock->save();
            }
        }

        // Search Ex Sales Value

        $list_stock_ex_sales = Stock::where('shares_id', '=', $shares_sell->shares_id)
            ->where('date', '<', $shares_sell->formulir->form_date)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $total_qty = 0;
        $subtotal = 0;
        foreach ($list_stock_ex_sales as $stock) {
            $total_qty += $stock->remaining_quantity;
            $subtotal_without_fee = $stock->remaining_quantity * $stock->price;
            $subtotal += $subtotal_without_fee + ($subtotal_without_fee * $stock->fee / 100);
        }

        $ex_sale = ($subtotal != 0) ? ($subtotal / $total_qty) : 0;
        foreach ($list_stock as $stock) {
            $stock->average_price = $ex_sale;
            $stock->save();
        }
    }

    public static function isQuantityAvailable($shares_sell)
    {
        $list_stock = Stock::where('shares_id', '=', $shares_sell->shares_id)
            ->where('owner_group_id', '=', $shares_sell->owner_group_id)
            ->where('owner_id', '=', $shares_sell->owner_id)
            ->where('broker_id', '=', $shares_sell->broker_id)
            ->where('date', '<', $shares_sell->formulir->form_date)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $selling_quantity = $shares_sell->quantity;

        foreach ($list_stock as $stock) {
            if ($selling_quantity >= 0) {
                $stock_fifo = new StockFifo;
                if ($selling_quantity >= $stock->remaining_quantity) {
                    $selling_quantity -= $stock->remaining_quantity;
                    $stock_fifo->quantity = $stock->remaining_quantity;
                    $stock->remaining_quantity = 0;
                } else {
                    $stock->remaining_quantity -= $selling_quantity;
                    $stock_fifo->quantity = $selling_quantity;
                    $selling_quantity = 0;
                }
            }
        }

        if ($selling_quantity > 0) {
            return false;
        }

        return true;
    }

    private static function getLastStock($shares_trading)
    {
        return Stock::where('owner_group_id', '=', $shares_trading->owner_group_id)
            ->where('broker_id', '=', $shares_trading->broker_id)
            ->where('shares_id', '=', $shares_trading->shares_id)
            ->where('owner_id', '=', $shares_trading->owner_id)
            ->where('date', '<', $shares_trading->formulir->form_date)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    private static function recalculate($shares_trading)
    {
        if (Stock::where('shares_id', '=', $shares_trading->shares_id)
                ->where('owner_group_id', '=', $shares_trading->owner_group_id)
                ->where('owner_id', '=', $shares_trading->owner_id)
                ->where('date', '>', $shares_trading->formulir->form_date)
                ->get()
                ->count() > 0) {
            return 1;
        }

        return 0;
    }

    public static function remove($formulir_id)
    {
        $list_stock_fifo = StockFifo::where('shares_out_id', '=', $formulir_id)->get();

        foreach ($list_stock_fifo as $stock_fifo) {
            $stock = Stock::where('formulir_id', '=', $stock_fifo->shares_in_id)->first();
            $stock->remaining_quantity += $stock_fifo->quantity;
            $stock->save();

            $stock_fifo->delete();
        }
    }

    public static function clear($formulir_id)
    {
        $stock = Stock::where('formulir_id', '=', $formulir_id)->first();
        if (! $stock) {
            return false;
        }

        if ($stock->remaining_quantity != $stock->quantity) {
            throw new PointException('This Shares already sold');
        }
        $stock->delete();
    }
}
