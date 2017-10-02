<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\UserWarehouse;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\Invoice;
use Point\PointPurchasing\Models\Inventory\InvoiceItem;
use Point\PointPurchasing\Models\Inventory\PurchaseOrderItem;

class InvoiceHelper
{
    public static function searchList($list_invoice, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($order_by) {
            $list_invoice = $list_invoice->orderBy($order_by, $order_type);
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
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_invoice;
    }

    public static function create(Request $request, $formulir, $reference = null)
    {
        $invoice = new Invoice;
        $invoice->formulir_id = $formulir->id;
        $invoice->supplier_id = $request->input('supplier_id');
        $invoice->due_date = date_format_db($request->input('due_date'), $request->input('time'));
        $invoice->type_of_tax = $request->input('type_of_tax');
        $invoice->expedition_fee = number_format_db($request->input('expedition_fee'));
        $invoice->discount = $request->input('discount');
        $invoice->save();
        $subtotal = 0;
        for ($i=0 ; $i < count($request->input('item_id')) ; $i++) {
            $invoice_item = new InvoiceItem;
            $invoice_item->point_purchasing_invoice_id = $invoice->id;
            $invoice_item->item_id = $request->input('item_id')[$i];
            $invoice_item->allocation_id = $request->input('allocation_id')[$i];
            $invoice_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $invoice_item->price = number_format_db($request->input('item_price')[$i]);
            $invoice_item->discount = number_format_db($request->input('item_discount')[$i]);
            $invoice_item->unit = $request->input('item_unit')[$i];
            $invoice_item->converter = 1;
            $invoice_item->save();

            // Insert to Allocation Report
            $amount = ($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price /100 * $invoice_item->discount);
            AllocationHelper::save($invoice->formulir_id, $invoice_item->allocation_id, $amount);

            if ($reference != null) {
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

            $subtotal += $amount;
        }

        if ($reference != null) {
            formulir_lock($reference->formulir_id, $invoice->formulir_id);
            $reference->formulir->form_status = 1;
            $reference->formulir->save();
        }

        $formulir->approval_status = 1;
        $formulir->save();

        $discount = $subtotal * $request->input('discount')/100;
        $tax_base = $subtotal - $discount;
        $tax = 0;

        if ($invoice->type_of_tax == 'exclude') {
            $tax = $tax_base * 10 / 100;
        }
        if ($invoice->type_of_tax == 'include') {
            $tax_base = $tax_base * 100 / 110;
            $tax = $tax_base * 10 / 100;
        }

        $invoice->subtotal = $subtotal;
        $invoice->tax_base = $tax_base;
        $invoice->tax = $tax;
        $invoice->total = $tax_base + $tax + $invoice->expedition_fee;
        $invoice->save();

        /**
         * jika terjadi perubahan discount dan tax di invoice, maka itu dijournal ulang
         * tapi masalahnya ada 1 PO bisa mempunyai beberap LPB, dan sistem yang sekrang 1 LPB satu invoice.
         * Solusi apa yang tepat untuk hal ? Sementar di PO bisa terjadi perubahan tax dan discount
         *
         * Jika terjadi perubahan di invoice, maka harus di
         */
        $changed = false;
        if (($request->input('original_expedition_fee') != $invoice->expedition_fee) || ($request->input('original_tax_type') != $invoice->type_of_tax) || ($request->input('original_discount') != $invoice->discount)) {
            self::rejournal($request, $invoice);
            $changed = true;
        }

        // if price has modyfied, journal again
        for ($i=0; $i < count($request->input('item_id')); $i++) {
            if (($request->input('item_price_original')[$i] != number_format_db($request->input('item_price')[$i])) && $changed == false) {
                self::journalDifferences($invoice, number_format_db($request->input('item_price')[$i]), $request->input('item_id')[$i]);
                $changed == true;
            }
        }

        JournalHelper::checkJournalBalance($invoice->formulir_id);
        return $invoice;
    }

    public static function rejournal($request, $invoice)
    {
        $goods_received = $request->input('reference_type')::find($request->input('reference_id'));
        if (! $goods_received) {
            throw new PointException("REFERENCE NOT FOUND");
        }

        Journal::where('form_journal_id', $goods_received->formulir_id)->delete();
        AccountPayableAndReceivable::where('formulir_reference_id', $goods_received->formulir_id)->delete();
        Inventory::where('formulir_id', $goods_received->formulir_id)->delete();

        $total_quantity = InvoiceItem::where('point_purchasing_invoice_id', $invoice->id)->selectRaw('sum(quantity) as quantity')->first()->quantity;

        $subtotal = 0;
        foreach ($invoice->items as $invoice_item) {
            $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
            // Journal inventory
            $total_per_row = $invoice_item->quantity * $invoice_item->price - $invoice_item->quantity * $invoice_item->price / 100 * $invoice_item->discount;
            if ($invoice->discount) {
                $discounty = $total_per_row * $invoice->discount / 100;
                $total_per_row = $total_per_row - $discounty;
            }

            if ($invoice->type_of_tax == 'include') {
                $total_per_row = $total_per_row * 100 / 110;
            }
            $subtotal += $total_per_row;

            $position = JournalHelper::position($invoice_item->item->account_asset_id);
            $journal = new Journal();
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = $invoice_item->item->account_asset_id;
            $journal->description = 'Goods Received [' . $invoice->formulir->form_number.']';
            $journal->$position = $total_per_row + $invoice->expedition_fee * $invoice_item->quantity / $total_quantity;
            $journal->form_journal_id = $invoice->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $invoice_item->item_id;
            $journal->subledger_type = get_class($invoice_item);
            $journal->save();

            \Log::info('sedian '. $position. ' ' . $journal->$position);
            // insert new inventory
            $item = Item::find($invoice_item->item_id);
            $inventory = new Inventory();
            $inventory->formulir_id = $invoice->formulir->id;
            $inventory->item_id = $item->id;
            $inventory->quantity = $invoice_item->quantity * $invoice_item->converter;
            $inventory->price = $invoice_item->price / $invoice_item->converter;
            $inventory->form_date = $invoice->formulir->form_date;
            $inventory->warehouse_id = $goods_received->warehouse_id;
            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->in();
        }

        $account_receiveable = JournalHelper::getAccount('point purchasing', 'account payable');
        $position = JournalHelper::position($account_receiveable);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $account_receiveable;
        $journal->description = 'Goods Received Purchasing [' . $invoice->formulir->form_number.']';
        $journal->$position = $invoice->total;
        $journal->form_journal_id = $invoice->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id = $invoice->supplier_id;
        $journal->subledger_type = get_class($invoice->supplier);
        $journal->save();

        \Log::info('account payable '. $position. ' ' . $journal->$position);

        if ($invoice->tax != 0) {
            $income_tax_receiveable = JournalHelper::getAccount('point purchasing', 'income tax receivable');
            $position = JournalHelper::position($income_tax_receiveable);
            $journal = new Journal;
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = $income_tax_receiveable;
            $journal->description = 'Goods Received Purchasing [' . $invoice->formulir->form_number.']';
            $journal->$position = $invoice->tax;
            $journal->form_journal_id = $invoice->formulir->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();   
            \Log::info('tax '. $position. ' ' . $journal->$position);
        }
    }

    public static function journalDifferences($invoice, $item_price, $item_id)
    {
        $item = Item::find($item_id);
        $position = JournalHelper::position($item->account_asset_id);
        $journal = new Journal();
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $item->account_asset_id;
        $journal->description = 'Invoice Purchasing [' . $invoice->formulir->form_number.']';
        $journal->$position = $item_price * -1;
        $journal->form_journal_id = $invoice->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id = $item_id;
        $journal->subledger_type = get_class($item);
        $journal->save();

        $expedition = JournalHelper::getAccount('point purchasing', 'inventory differences');
        $position = JournalHelper::position($expedition);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $expedition;
        $journal->description = 'Invoice Purchasing [' . $invoice->formulir->form_number.']';
        $journal->$position = $item_price;
        $journal->form_journal_id = $invoice->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        JournalHelper::checkJournalBalance($invoice->formulir_id);
    }

    public static function searchPurchaseOrderDetail($goods_received, $goods_received_item)
    {
        $purchase_order = $goods_received->purchaseOrder;
        $purchase_order_item = PurchaseOrderItem::where('point_purchasing_order_id', $purchase_order->id)->where('item_id', $goods_received_item->item_id)->first();
        return $purchase_order_item;
    }
}
