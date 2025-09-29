<?php
namespace Point\PointSales\Helpers;

use Point\Core\Exceptions\PointException;
use Point\Core\Helpers\TempDataHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\ItemUnit;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointSales\Models\Pos\Pos;
use Point\PointSales\Models\Pos\PosItem;

class PosHelper
{
    public static function searchList($list_pos_sales, $order_by = 'desc', $order_type = 'form_date', $date_from, $date_to, $search, $status)
    {
        if ($order_by) {
            $list_pos_sales = $list_pos_sales->orderBy($order_by, $order_type);
        } else {
            $list_pos_sales = $list_pos_sales->orderByStandard();
        }

        $list_pos_sales = $list_pos_sales->where('formulir.form_status', '=', $status ?: 0);

        if ($date_from) {
            $list_pos_sales = $list_pos_sales->where('formulir.form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_pos_sales = $list_pos_sales->where('formulir.form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_pos_sales = $list_pos_sales->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                    ->orWhere('person.name', 'like', '%'.$search.'%')
                    ->orWhere('person.code', 'like', '%'.$search.'%')
                    ->orWhere('item.name', 'like', '%'.$search.'%')
                    ->orWhere('item.code', 'like', '%'.$search.'%');
            });
        }

        return $list_pos_sales;
    }

    public static function getItem($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return false;
        }
        return $item;
    }

    public static function getWarehouse()
    {
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if (! $warehouse_id) {
            return false;
        }
        return $warehouse_id;
    }

    public static function validate($request, $item_id, $quantity, $customer_id, $price, $money_received)
    {
        if (!self::getCustomer()) {
            throw new PointException("PLEASE FILL IN THE FIELDS CORRECTLY");
        }

        if (!isset($item_id) || !isset($quantity) || !isset($customer_id) || !isset($price) || !isset($money_received)) {
            throw new PointException("PLEASE FILL IN THE FIELDS CORRECTLY");
        }

        if ($money_received < number_format_db($request->input('foot_total'))) {
            throw new PointException("FAILED, CASH PAYMENT LESS");
        }
    }

    public static function create($request, $formulir)
    {
        $item_id = $request->input('item_id');
        $warehouse_id = UserWarehouse::where('user_id', auth()->user()->id)->first()->warehouse_id;
        $quantity = $request->input('quantity');
        $price = $request->input('price');
        $discount = $request->input('discount');
        $customer_id = self::getCustomer();
        $money_received = number_format_db($request->input('foot_money_received'));

        self::validate($request, $item_id, $quantity, $customer_id, $price, $money_received);
        
        $subtotal = 0;
        for ($i=0;$i < count($item_id); $i++) {
            $subtotal += (number_format_db($quantity[$i]) * number_format_db($price[$i])) - (number_format_db($quantity[$i]) * number_format_db($price[$i])/100 * number_format_db($discount[$i]));
        }

        $discount = $subtotal * number_format_db($request->input('foot_discount')) / 100;
        $tax_base = $subtotal - $subtotal * number_format_db($request->input('foot_discount')) / 100;
        $tax = 0;

        if ($request->input('tax_type') == 'include') {
            $tax_base = $tax_base * 100 / 110;
            $tax = $tax_base * 10 / 100;
        }

        if ($request->input('tax_type') == 'exclude') {
            $tax = $tax_base * 10 / 100;
        }

        $total = $tax_base + $tax;

        $pos = new Pos;
        $pos->formulir_id = $formulir->id;
        $pos->subtotal = $subtotal;
        $pos->discount = number_format_db($request->input('foot_discount'));
        $pos->tax_base = $tax_base;
        $pos->tax = $tax;
        $pos->tax_type = $request->input('tax_type');
        $pos->total = $total;
        $pos->customer_id = $customer_id;
        $pos->money_received = $money_received;
        $pos->warehouse_id = $warehouse_id;
        $pos->save();

        $formulir->form_status = 0;
        $cost_of_sales = self::storePosItem($request, $pos, $formulir, $item_id, $quantity, $discount, $customer_id, $price, $money_received, $warehouse_id);
        
        $print = false;
        if ($request->input('action') == 'save') {
            $formulir->form_status = 1;
            $cost_of_sales = self::journalInventory($request, $pos, $formulir, $item_id, $quantity, $discount, $customer_id, $price, $money_received, $warehouse_id);
            self::journalPos($request, $pos, $formulir, $cost_of_sales, $item_id, $quantity, $discount, $customer_id, $price, $money_received, $warehouse_id);

            if ($request->input('print')) {
                $print = true;
            }
        }

        if ($request->input('action') == 'cancel') {
            $formulir->form_status = -1;
        }

        $formulir->save();
        
        if (!TempDataHelper::get('pos', auth()->user()->id, ['is_pagination' => true])) {
            throw new PointException('NO GOODS FOR SALES');
        }
        

        return ['pos' => $pos, 'print' => $print];
    }

    public static function storePosItem($request, $pos, $formulir, $item_id, $quantity, $discount, $customer_id, $price, $money_received, $warehouse_id)
    {
        $cost_of_sales = 0;
        for ($i=0;$i < count($item_id); $i++) {
            if ($quantity[$i] > 0) {
                $item = Item::find($item_id[$i]);

                $pos_item = new PosItem;
                $pos_item->pos_id = $pos->id;
                $pos_item->item_id = $item_id[$i];
                $pos_item->warehouse_id = $warehouse_id;
                $pos_item->quantity = number_format_db($quantity[$i]);
                $pos_item->price = number_format_db($price[$i]);
                $pos_item->discount = number_format_db($discount[$i]);
                $pos_item->unit = $item->defaultUnit($item->id)->name;
                $pos_item->converter = 1;
                $pos_item->save();
            }
        }

        return $cost_of_sales;
    }

    public static function journalInventory($request, $pos, $formulir, $item_id, $quantity, $discount, $customer_id, $price, $money_received, $warehouse_id)
    {
        $cost_of_sales = 0;
        for ($i=0;$i < count($item_id); $i++) {
            // inventory control
            $inventory = new Inventory;

            $inventory->formulir_id = $formulir->id;
            $inventory->item_id = $item_id[$i];
            $inventory->quantity = number_format_db($quantity[$i]);
            $inventory->price = number_format_db($price[$i]);
            $inventory->form_date = date('Y-m-d H:i:s');
            $inventory->warehouse_id = $warehouse_id;

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->out();

            $cost = InventoryHelper::getCostOfSales(\Carbon::now(), $inventory->item_id, $warehouse_id) * abs($inventory->quantity);
            $cost_of_sales += $cost;

            // JOURNAL #5 of #6 - INVENTORY
            $journal = new Journal;
            $journal->form_date = $pos->formulir->form_date;
            $journal->coa_id = $inventory->item->account_asset_id;
            $journal->description = 'point of sales "' . $inventory->item->codeName.'"';
            $journal->credit = $cost;
            $journal->form_journal_id = $pos->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $inventory->item_id;
            $journal->subledger_type = get_class($inventory->item);
            $journal->save();
        }

        return $cost_of_sales;
    }

    public static function journalPos($request, $pos, $formulir, $cost_of_sales, $item_id, $quantity, $discount, $customer_id, $price, $money_received, $warehouse_id)
    {
        /**
         * COA CATEGORY     | ACCOUNT               | DEBIT         | CREDIT
         * ------------------------------------------------------------------
         * CURRENT ASSET    | PETTY CASH            | xxxx          |
         * LIABILITY        | INCOME TAX PAYABLE    |               | xxxx
         * INCOME           | SALE OF GOODS         |               | xxxx
         * EXPENSE          | SALES DISCOUNT        | xxxx          |
         * CURRENT ASSET    | INVENTORY             |               | xxxx
         * EXPENSE          | COST OF SALES         | xxxx          |
         * ------------------------------------------------------------------
         */
        if ($pos->tax_type == 'include') {
            $pos->subtotal = $pos->tax_base;
        }

        // JOURNAL #1 of #6 - PETTY CASH
        $warehouse = Warehouse::find($warehouse_id);
        if (! $warehouse->petty_cash_account) {
            throw new PointException('PLEASE SET PETTY CASH ACCOUNT FOR YOUR WAREHOUSE');
        }
        $journal = new Journal;
        $journal->form_date = $pos->formulir->form_date;
        $journal->coa_id = $warehouse->petty_cash_account;
        $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
        $journal->debit = $pos->total;
        $journal->form_journal_id = $pos->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        // JOURNAL #2 of #6 - INCOME TAX PAYABLE
        if ($pos->tax != 0) {
            $income_tax_payable_account = JournalHelper::getAccount('point sales pos', 'income tax payable');
            $journal = new Journal();
            $journal->form_date = $pos->formulir->form_date;
            $journal->coa_id = $income_tax_payable_account;
            $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
            $journal->credit = $pos->tax;
            $journal->form_journal_id = $pos->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        // JOURNAL #3 of #6 - SALE OF GOODS
        $sale_of_goods_account = JournalHelper::getAccount('point sales pos', 'sale of goods');
        $journal = new Journal;
        $journal->form_date = $pos->formulir->form_date;
        $journal->coa_id = $sale_of_goods_account;
        $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
        $journal->credit = $pos->subtotal;
        $journal->form_journal_id = $pos->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        // JOURNAL #4 of #6 - SALE DISCOUNT
        $sale_discount_account = JournalHelper::getAccount('point sales pos', 'sales discount');
        $journal = new Journal;
        $journal->form_date = $pos->formulir->form_date;
        $journal->coa_id = $sale_discount_account;
        $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
        $journal->debit = $pos->subtotal * $pos->discount / 100;
        $journal->form_journal_id = $pos->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        // JOURNAL #6 of #6 - COST OF SALES
        $cost_of_sales_account = JournalHelper::getAccount('point sales pos', 'cost of sales');
        $journal = new Journal;
        $journal->form_date = $pos->formulir->form_date;
        $journal->coa_id = $cost_of_sales_account;
        $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
        $journal->debit = $cost_of_sales;
        $journal->form_journal_id = $pos->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
    }

    public static function getCustomer()
    {
        $temp = TempDataHelper::get('pos', auth()->user()->id, ['is_pagination' => false]);
        if ($temp) {
            return $temp[0]['customer_id'];
        }

        return null;
    }
}
