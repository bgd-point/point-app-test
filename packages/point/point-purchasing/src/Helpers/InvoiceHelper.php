<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
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
        $invoice->expedition_fee = number_format_db($request->input('expedition_fee'));
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
        $tax_base = number_format_db($request->input('tax_base'));
        $tax = number_format_db($request->input('tax'));
        $total = number_format_db($request->input('total'));
        $invoice->subtotal = $subtotal;
        $invoice->discount = $request->input('discount');
        $invoice->tax_base = $tax_base;
        $invoice->tax = $tax;
        $invoice->type_of_tax = $request->input('type_of_tax');
        
        $invoice->total = $tax_base + $tax + $invoice->expedition_fee;
        $invoice->save();

        /**
         * jika terjadi perubahan discount dan tax di invoice, maka itu dijournal ulang
         * tapi masalahnya ada 1 PO bisa mempunyai beberap LPB, dan sistem yang sekrang 1 LPB satu invoice.
         * Solusi apa yang tepat untuk hal ? Sementar di PO bisa terjadi perubahan tax dan discount
         *
         * Jika terjadi perubahan di invoice, maka harus di
         */

        // if price has modyfied, journal again
        for ($i=0 ; $i < count($request->input('item_id')) ; $i++) {
            if ($request->input('item_price_original')[$i] != number_format_db($request->input('item_price')[$i])) {
                self::journalDifferences($invoice, number_format_db($request->input('item_price')[$i]), $request->input('item_id')[$i]);
            }
        }

        return $invoice;
    }

    public static function journalDifferences($invoice, $item_price, $item_id)
    {
        /**
         * Journal selisih
         * 1. utang expedisi
         * 2. sedian
         * 3. selisih
         */

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
