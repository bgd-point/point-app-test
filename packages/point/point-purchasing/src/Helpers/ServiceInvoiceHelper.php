<?php

namespace Point\PointPurchasing\Helpers;

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
use Point\PointPurchasing\Models\Service\Invoice;
use Point\PointPurchasing\Models\Service\InvoiceItem;
use Point\PointPurchasing\Models\Service\InvoiceService;

class ServiceInvoiceHelper
{
    public static function searchList($list_invoice, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($order_by) {
            $list_invoice = $list_invoice->orderBy($order_by, $order_type);
        } else {
            $list_invoice = $list_invoice->orderByStandard();
        }
        
        if ($status == 'report') {
            $list_invoice = $list_invoice->whereIn('formulir.form_status', [0, 1]);
        }

        if (($status != 'all') && ($status != 'report')) {
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
        $invoice->due_date = date_format_db($request->input('due_date'));
        $invoice->save();

        $subtotal = 0;
        $subtotal_service = 0;
        $amount = 0;
        for ($i=0 ; $i < count($request->input('service_id')) ; $i++) {
            $service = Service::find($request->input('service_id')[$i]);
            $invoice_service = new InvoiceService;
            $invoice_service->point_purchasing_service_invoice_id = $invoice->id;
            $invoice_service->service_id = $service->id;
            $invoice_service->quantity = number_format_db($request->input('service_quantity')[$i]);
            $invoice_service->price = number_format_db($request->input('service_price')[$i]);
            $invoice_service->discount = number_format_db($request->input('service_discount')[$i]);
            $invoice_service->service_notes = $request->input('service_notes')[$i];
            $invoice_service->allocation_id = $request->input('service_allocation_id')[$i];
            $invoice_service->save();

            $amount = ($invoice_service->quantity * $invoice_service->price) - ($invoice_service->quantity * $invoice_service->price/100 * $invoice_service->discount);
            AllocationHelper::save($invoice->formulir_id, $invoice_service->allocation_id, $amount, $invoice_service->service_notes);

            $subtotal_service += $amount;
        }
        for ($i=0 ; $i < count($request->input('item_id')) ; $i++) {
            $item = Item::find($request->input('item_id')[$i]);
            $item_unit = ItemUnit::where('item_id', $item->id)->first();
            $invoice_item = new InvoiceItem;
            $invoice_item->point_purchasing_service_invoice_id = $invoice->id;
            $invoice_item->item_id = $item->id;
            $invoice_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $invoice_item->price = number_format_db($request->input('item_price')[$i]);
            $invoice_item->discount = number_format_db($request->input('item_discount')[$i]);
            $invoice_item->unit = $item_unit->name;
            $invoice_item->converter = $item_unit->converter;
            $invoice_item->item_notes = $request->input('item_notes')[$i];
            $invoice_item->allocation_id = $request->input('allocation_id')[$i];
            $invoice_item->save();

            $total_per_row = $invoice_item->quantity * $invoice_item->price - $invoice_item->quantity * $invoice_item->price / 100 * $invoice_item->discount;
            AllocationHelper::save($invoice->formulir_id, $invoice_item->allocation_id, $total_per_row, $invoice_item->item_notes);
            
            if ($request->input('type_of_tax') == 'include') {
                $total_per_row = $total_per_row * 100 / 110;
            }
            // Journal
            $position = JournalHelper::position($invoice_item->item->account_asset_id);
            $journal = new Journal();
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = $invoice_item->item->account_asset_id;
            $journal->description = 'Purchasing Service Invoice Item [' . $invoice->formulir->form_number.']';
            $journal->$position = $total_per_row;
            $journal->form_journal_id = $invoice->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $invoice_item->item_id;
            $journal->subledger_type = get_class($invoice_item->item);
            $journal->save();

            // insert new inventory
            $item = Item::find($invoice_item->item_id);
            $inventory = new Inventory();
            $inventory->formulir_id = $formulir->id;
            $inventory->item_id = $item->id;
            $inventory->quantity = $invoice_item->quantity * $invoice_item->converter;
            $inventory->price = $invoice_item->price / $invoice_item->converter;
            $inventory->form_date = $formulir->form_date;
            $inventory->warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->in();


            $subtotal += ($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price/100 * $invoice_item->discount);
        }

        $subtotal = $subtotal + $subtotal_service;
        $discount = $subtotal * $request->input('discount') / 100;
        $tax_base = $subtotal - $subtotal * $request->input('discount') / 100;
        $tax = 0;

        if ($request->input('type_of_tax') == 'include') {
            $tax_base = $tax_base * 100 / 110;
            $tax = $tax_base * 10 / 100;
            $subtotal_service = $subtotal_service * 100 / 110;
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

        $data = array(
            'value_of_account_payable' => $total,
            'value_of_income_tax_receiveable' => $tax,
            'value_of_discount' => $discount * (-1),
            'value_cost_of_service' => $subtotal_service,
            'request' => $request,
            'formulir' => $formulir,
            'invoice' => $invoice
        );
        self::journal($data);
        
        JournalHelper::checkJournalBalance($invoice->formulir_id);
        return $invoice;
    }

    public static function journal($data)
    {
        // 1. Journal account payable
        $account_payable = JournalHelper::getAccount('point purchasing service', 'account payable');
        $position = JournalHelper::position($account_payable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_payable;
        $journal->description = 'Service Invoice Purchasing [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_payable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->supplier);
        $journal->save();

        // 2. Journal income tax receivable
        if ($data['invoice']->tax > 0) {
            $income_tax_receiveable = JournalHelper::getAccount('point purchasing service', 'income tax receivable');
            $position = JournalHelper::position($income_tax_receiveable);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $income_tax_receiveable;
            $journal->description = 'Service Invoice Purchasing [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_income_tax_receiveable'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }
        // 3. Journal Purchase Discount
        if ($data['invoice']->discount > 0) {
            $purchasing_discount = JournalHelper::getAccount('point purchasing service', 'purchase discount');
            $position = JournalHelper::position($purchasing_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $purchasing_discount;
            $journal->description = 'Service invoice purchasing [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        $cost_of_service = JournalHelper::getAccount('point purchasing service', 'service cost');
        $position = JournalHelper::position($cost_of_service);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $cost_of_service;
        $journal->description = 'Service invoice purchasing [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_cost_of_service'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
    }
}
