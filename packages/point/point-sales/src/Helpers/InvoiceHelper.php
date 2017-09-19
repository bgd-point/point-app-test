<?php

namespace Point\PointSales\Helpers;

use Illuminate\Http\Request;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\ItemUnit;
use Point\Framework\Models\Master\UserWarehouse;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\InvoiceItem;

class InvoiceHelper
{
    public static function searchList($list_invoice,  $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_invoice = $list_invoice->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $list_invoice = $list_invoice->orderBy($order_by, $order_type);
        } else {
            $list_invoice = $list_invoice->orderByStandard();
        }

        if ($date_from) {
            $list_invoice = $list_invoice->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_invoice = $list_invoice->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_invoice = $list_invoice->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_invoice;
    }

    public static function create(Request $request, $formulir, $references = null)
    {
        $invoice = new Invoice;
        $invoice->formulir_id = $formulir->id;
        $invoice->due_date = date_format_db($request->input('due_date'));
        $invoice->person_id = $request->input('person_id');
        $invoice->expedition_fee = number_format_db($request->input('expedition_fee'));
        $invoice->save();

        $subtotal = 0;
        $amount = 0;
        for ($i=0 ; $i < count($request->input('item_id')) ; $i++) {
            $invoice_item = new InvoiceItem;
            $invoice_item->point_sales_invoice_id = $invoice->id;
            $invoice_item->item_id = $request->input('item_id')[$i];
            $invoice_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $invoice_item->price = number_format_db($request->input('item_price')[$i]);
            $invoice_item->discount = number_format_db($request->input('item_discount')[$i]);
            $invoice_item->unit = $request->input('item_unit')[$i];
            $invoice_item->allocation_id = $request->input('allocation_id')[$i];
            $invoice_item->converter = 1;
            $invoice_item->save();

            if ($references != null) {
                ReferHelper::create(
                    $request->input('reference_item_type')[$i],
                    $request->input('reference_item_id')[$i],
                    get_class($invoice_item),
                    $invoice_item->id,
                    get_class($invoice),
                    $invoice->id,
                    $invoice_item->quantity
                );
            }
            
            $amount = ($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price/100 * $invoice_item->discount);
            AllocationHelper::save($invoice->formulir_id, $invoice_item->allocation_id, $amount);
            $subtotal += $amount;
        }

        if ($references != null) {
            foreach ($references as $reference) {
                formulir_lock($reference->formulir_id, $invoice->formulir_id);
            }
        }
        $formulir->approval_status = 1;
        $formulir->save();
        
        $discount = $subtotal * $request->input('discount')/100;
        $tax_base = number_format_db($request->input('tax_base'));
        $tax = number_format_db($request->input('tax'));
        $total = number_format_db($request->input('total'));

        $invoice->subtotal = $subtotal;
        $invoice->discount = $request->input('discount');
        $invoice->tax_base = $tax_base;
        $invoice->tax = $tax;
        $invoice->type_of_tax = $request->input('type_of_tax');
        $invoice->total = $total;
        $invoice->save();
        
        $cost_of_sales = 0;
        foreach ($invoice->items as $invoice_detail) {
            // insert new inventory
            $item = Item::find($invoice_detail->item_id);
            $inventory = new Inventory();
            $inventory->formulir_id = $formulir->id;
            $inventory->item_id = $item->id;
            $inventory->quantity = $invoice_detail->quantity * $invoice_detail->converter;
            $inventory->price = $invoice_detail->price / $invoice_detail->converter;
            $inventory->form_date = $formulir->form_date;
            $inventory->warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->out();

            $cost = InventoryHelper::getCostOfSales(\Carbon::now(), $inventory->item_id, $inventory->warehouse_id) * abs($inventory->quantity);
            $cost_of_sales += $cost;

            $journal = new Journal;
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = $inventory->item->account_asset_id;
            $journal->description = 'invoice "' . $inventory->item->codeName.'"';
            $journal->credit = $cost;
            $journal->form_journal_id = $invoice->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $inventory->item_id;
            $journal->subledger_type = get_class($inventory->item);
            $journal->save();

            \Log::info('inventory credit '. $journal->credit);

        }
        
        // Journal tax exclude and non-tax
        if ($request->input('type_of_tax') == 'exclude' || $request->input('type_of_tax') == 'non') {
            $data = array(
                'value_of_account_receivable' => $total,
                'value_of_income_tax_payable' => $tax,
                'value_of_sale_of_goods' => $subtotal,
                'value_of_cost_of_sales' => $cost_of_sales,
                'value_of_discount' => $discount * (-1),
                'value_of_expedition_income' => $invoice->expedition_fee,
                'formulir' => $formulir,
                'invoice' => $invoice
            );
            self::journal($data);
        }

        // Journal tax include
        if ($request->input('type_of_tax') == 'include') {
            $data = array(
                'value_of_account_receivable' => $total,
                'value_of_income_tax_payable' => $tax,
                'value_of_sale_of_goods' => $tax_base,
                'value_of_cost_of_sales' => $cost_of_sales,
                'value_of_discount' => $discount,
                'value_of_expedition_income' => $invoice->expedition_fee,
                'formulir' => $formulir,
                'invoice' => $invoice
            );
            self::journal($data);
        }
        
        JournalHelper::checkJournalBalance($invoice->formulir_id);
        return $invoice;
    }

    public static function journal($data)
    {
        // 1. Journal Account Receivable
        $account_receivable = JournalHelper::getAccount('point sales indirect', 'account receivable');
        $position = JournalHelper::position($account_receivable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_receivable;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_receivable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        // 2. Journal Income Tax  Payable
        if ($data['invoice']->tax != 0) {
            $income_tax_receivable = JournalHelper::getAccount('point sales indirect', 'income tax payable');
            $position = JournalHelper::position($income_tax_receivable);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $income_tax_receivable;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_income_tax_payable'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }
        
        // 3. Journal Sales Of Goods
        $sales_of_goods = JournalHelper::getAccount('point sales indirect', 'sale of goods');
        $position = JournalHelper::position($sales_of_goods);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $sales_of_goods;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_sale_of_goods'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        // 4. Journal Sales Discount
        if ($data['invoice']->discount > 0) {
            $sales_discount = JournalHelper::getAccount('point sales indirect', 'sales discount');
            $position = JournalHelper::position($sales_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $sales_discount;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        // 5. Journal Expedition Cost
        if ($data['invoice']->expedition_fee > 0) {
            $cost_of_sales = JournalHelper::getAccount('point sales indirect', 'expedition income');
            $position = JournalHelper::position($cost_of_sales);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $cost_of_sales;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_expedition_income'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        $cost_of_sales_account = JournalHelper::getAccount('point sales indirect', 'cost of sales');
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $cost_of_sales_account;
        $journal->description = 'invoice indirect sales "' . $data['formulir']->form_number.'"';
        $journal->debit = $data['value_of_cost_of_sales'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
    }
}
