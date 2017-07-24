<?php

namespace Point\PointSales\Helpers;

use Illuminate\Http\Request;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\ItemUnit;
use Point\Framework\Models\Master\Service;
use Point\Framework\Models\Master\UserWarehouse;
use Point\PointSales\Models\Service\Invoice;
use Point\PointSales\Models\Service\InvoiceItem;
use Point\PointSales\Models\Service\InvoiceService;

class ServiceInvoiceHelper
{
    public static function searchList($list_invoice, $orderBy, $orderType, $status = 0, $date_from, $date_to, $search)
    {
        if ($orderBy) {
            $list_invoice = $list_invoice->orderBy($orderBy, $orderType);
        } else {
            $list_invoice = $list_invoice->orderByStandard();
        }

        if ($status != 'all') {
            $list_invoice = $list_invoice->where('formulir.form_status', '=', $status ?: 0);
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
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                    ->orWhere('person.name', 'like', '%'.$search.'%');
            });
        }

        return $list_invoice;
    }

    public static function create(Request $request, $formulir)
    {
        $invoice = new Invoice;
        $invoice->formulir_id = $formulir->id;
        $invoice->person_id = $request->input('person_id');
        $invoice->save();

        $subtotal = 0;
        $subtotal_service = 0;
        $amount_service = 0;
        $amount_item = 0;
        for ($i=0 ; $i < count($request->input('service_id')) ; $i++) {
            $service = Service::find($request->input('service_id')[$i]);
            $invoice_service = new InvoiceService;
            $invoice_service->point_sales_service_invoice_id = $invoice->id;
            $invoice_service->service_id = $service->id;
            $invoice_service->quantity = number_format_db($request->input('service_quantity')[$i]);
            $invoice_service->price = number_format_db($request->input('service_price')[$i]);
            $invoice_service->discount = number_format_db($request->input('service_discount')[$i]);
            $invoice_service->service_notes = $request->input('service_notes')[$i];
            $invoice_service->allocation_id = $request->input('allocation_id')[$i];
            $invoice_service->save();

            $amount_service = ($invoice_service->quantity * $invoice_service->price) - ($invoice_service->quantity * $invoice_service->price/100 * $invoice_service->discount);
            $subtotal_service += $amount_service;
            // Insert to Allocation Report
            AllocationHelper::save($invoice->formulir->id, $invoice_service->allocation_id, $amount_service);
        }

        for ($i=0 ; $i < count($request->input('item_id')) ; $i++) {
            $item = Item::find($request->input('item_id')[$i]);
            $item_unit = ItemUnit::where('item_id', $item->id)->first();
            $invoice_item = new InvoiceItem;
            $invoice_item->point_sales_service_invoice_id = $invoice->id;
            $invoice_item->item_id = $item->id;
            $invoice_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $invoice_item->price = number_format_db($request->input('item_price')[$i]);
            $invoice_item->discount = number_format_db($request->input('item_discount')[$i]);
            $invoice_item->unit = $item_unit->name;
            $invoice_item->converter = $item_unit->converter;
            $invoice_item->item_notes = $request->input('item_notes')[$i];
            $invoice_item->allocation_id = $request->input('item_allocation_id')[$i];
            $invoice_item->save();

            $amount_item = ($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price/100 * $invoice_item->discount);
            $subtotal += $amount_item;
            // Insert to Allocation Report
            AllocationHelper::save($invoice->formulir->id, $invoice_item->allocation_id, $amount_item);
        }

        $subtotal = $subtotal + $subtotal_service;
        $discount = $subtotal * $request->input('discount') / 100;
        $tax_base = $subtotal - $subtotal * $request->input('discount') / 100;
        $tax = 0;

        if ($request->input('type_of_tax') == 'include') {
            $tax_base = $tax_base * 100 / 110;
            $tax = $tax_base * 10 / 100;
        }

        if ($request->input('type_of_tax') == 'exclude') {
            $tax = $tax_base * 10 / 100;
        }

        $total = $tax_base + $tax;

        $invoice->subtotal = $subtotal;
        $invoice->discount = $request->input('discount');
        $invoice->tax_base = $tax_base;
        $invoice->type_of_tax = $request->input('type_of_tax');
        $invoice->tax = $tax;
        $invoice->total = $total;
        $invoice->save();

        // Journal tax exclude and non-tax
        if ($request->input('type_of_tax') == 'exclude' || $request->input('type_of_tax') == 'non') {
            $data = array(
                'value_of_account_receivable' => $total,
                'value_of_income_tax_payable' => $tax,
                'value_of_sale_of_goods' => $subtotal,
                'value_of_discount' => $discount * (-1),
                'value_of_expedition_income' => $invoice->expedition_fee,
                'value_cost_of_sales' => $subtotal_service,
                'request' => $request,
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
                'value_of_discount' => $discount,
                'value_of_expedition_income' => $invoice->expedition_fee,
                'value_cost_of_sales' => $subtotal_service,
                'request' => $request,
                'formulir' => $formulir,
                'invoice' => $invoice
            );
            self::journal($data);
        }

        return $invoice;
    }

    public static function journal($data)
    {
        // 1. Journal Account Receivable
        $account_receivable = JournalHelper::getAccount('point sales service', 'account receivable');
        $position = JournalHelper::position($account_receivable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_receivable;
        $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_receivable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        // 2. Journal Income Tax Payable
        if ($data['invoice']->tax != 0) {
            $income_tax_receivable = JournalHelper::getAccount('point sales service', 'income tax payable');
            $position = JournalHelper::position($income_tax_receivable);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $income_tax_receivable;
            $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_income_tax_payable'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }
        
        // 3. Journal Sales of Goods
        $sales_of_goods = JournalHelper::getAccount('point sales service', 'sale of goods');
        $position = JournalHelper::position($sales_of_goods);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $sales_of_goods;
        $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_sale_of_goods'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        // 4. Journal Sales Discount
        if ($data['invoice']->discount > 0) {
            $sales_discount = JournalHelper::getAccount('point sales service', 'sales discount');
            $position = JournalHelper::position($sales_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $sales_discount;
            $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        self::journalInventory($data);
    }

    public static function journalInventory($data)
    {
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        for ($i=0;$i < count($data['request']->input('item_id')); $i++) {
            $quantity = number_format_db($data['request']->input('item_quantity')[$i]);
            $price = number_format_db($data['request']->input('item_price')[$i]);

            if ($quantity > 0) {
                // inventory control
                $inventory = new Inventory;
                $inventory->formulir_id = $data['formulir']->id;
                $inventory->item_id = $data['request']->input('item_id')[$i];
                $inventory->quantity = $quantity;
                $inventory->price = $price;
                $inventory->form_date = $data['formulir']->form_date;
                $inventory->warehouse_id = $warehouse_id;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->out();

                $cost = InventoryHelper::getCostOfSales(\Carbon::now(), $inventory->item_id, $warehouse_id) * $inventory->quantity;

                // JOURNAL #5 of #6 - INVENTORY
                $journal = new Journal;
                $journal->form_date = $data['formulir']->form_date;
                $journal->coa_id = $inventory->item->account_asset_id;
                $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
                $journal->credit = $cost;
                $journal->form_journal_id = $data['formulir']->id;
                $journal->form_reference_id;
                $journal->subledger_id = $inventory->item_id;
                $journal->subledger_type = get_class($inventory->item);
                $journal->save();

                // 5. Journal cost of sales
                $sales_discount = JournalHelper::getAccount('point sales service', 'cost of sales');
                $position = JournalHelper::position($sales_discount);
                $journal = new Journal;
                $journal->form_date = $data['formulir']->form_date;
                $journal->coa_id = $sales_discount;
                $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
                $journal->$position = $cost;
                $journal->form_journal_id = $data['formulir']->id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }
        }
    }
}
