<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Helpers\ExpeditionOrderHelper;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderGroup;
use Point\PointExpedition\Models\ExpeditionOrderGroupDetail;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointExpedition\Models\ExpeditionOrderReference;
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

    public static function create(Request $request, $formulir, $reference = null, $invoice_old = null)
    {
        $invoice = new Invoice;
        $invoice->formulir_id = $formulir->id;
        $invoice->supplier_id = $request->input('supplier_id');
        $invoice->due_date = date_format_db($request->input('due_date'), $request->input('time'));
        $invoice->type_of_tax = $request->input('type_of_tax') ? : 'non';
        $invoice->expedition_fee = $request->input('expedition_fee') ? number_format_db($request->input('expedition_fee')) : 0;
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

        // If not order expedition
        if ($request->input('include_expedition')) {
            $reset_journal = false;
            if ($invoice_old) {
                if ($invoice_old->is_reset_journal) {
                    $reset_journal = true;
                }
            }

            $changed = false;
            if (($request->input('original_expedition_fee') != $invoice->expedition_fee) || ($request->input('original_tax_type') != $invoice->type_of_tax) || ($request->input('original_discount') != $invoice->discount) || $reset_journal) {
                self::rejournal($request, $invoice);
                $invoice->is_reset_journal = true;
                $invoice->save();
                $changed = true;
            }

            // if price has modyfied, journal again
            for ($i=0; $i < count($request->input('item_id')); $i++) {
                if (($request->input('item_price_original')[$i] != number_format_db($request->input('item_price')[$i])) && $changed == false && $reset_journal == false) {
                    self::journalDifferences($invoice, number_format_db($request->input('item_price')[$i]), $request->input('item_id')[$i]);
                    $changed = true;
                }
            }
        }

        /**
         * If Order Expedition
         * - remove journal expedition order when price in purchase order not balance with price in invoice
         * - remove journal expedition order when tax, discount is modified
         * - rejournal again with expedition order
         */
        if (! $request->input('include_expedition')) {
            if (($request->input('original_tax_type') != $invoice->type_of_tax) || ($request->input('original_discount') != $invoice->discount)) {
                $goods_received = $request->input('reference_type')::find($request->input('reference_id'));
                $data = self::removeJournalExpeditionOrder($goods_received);

                self::fixSeederExpedition($invoice, $goods_received, $data['list_expedition_order'], $data['purchase_order']);
                self::rejournalExpeditionOrder($invoice, $goods_received, $data['list_expedition_order'], $data['purchase_order']);
            }
        }

        JournalHelper::checkJournalBalance($invoice->formulir_id);
        return $invoice;
    }

    /**
     * Rejournal if include expedition
     */
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

    /**
     * Rejournal if exclude expedition
     */
    public static function removeJournalExpeditionOrder($goods_received)
    {
        /**
         * Information :
         * One goods received have multiple expedition order
         * One invoice only one goods received
         */
        
        $list_expedition_order = FormulirLock::join('formulir', 'formulir.id', '=', 'formulir_lock.locked_id')
            ->join('point_expedition_order', 'point_expedition_order.id', '=', 'formulir.formulirable_id')
            ->where('locking_id', $goods_received->formulir_id)
            ->where('formulir.formulirable_type', get_class(new ExpeditionOrder))
            ->whereIn('formulir.form_status', [1, 0])
            ->where('formulir.approval_status', 1)
            ->whereNotNull('formulir.form_number')
            ->where('point_expedition_order.is_finish', 1)
            ->select('point_expedition_order.*');

        $expedition_order = $list_expedition_order->first();
        if (!$expedition_order) {
            return false;
        }

        // $expedition_order = ExpeditionOrder::find($expedition_order->id);
        // ExpeditionOrderHelper::removeJournal($expedition_order);
        $formulir_reference = Formulir::find($expedition_order->form_reference_id);
        $purchase_order = $formulir_reference->formulirable_type::find($formulir_reference->formulirable_id);

        $expedition_order_group_item = ExpeditionOrderGroupDetail::where('point_expedition_order_id', $expedition_order->id)->first();
        if (!$expedition_order_group_item) {
            return true;
        }

        Journal::where('form_journal_id', $expedition_order_group_item->group->formulir_id)->delete();
        AccountPayableAndReceivable::where('formulir_reference_id', $expedition_order_group_item->group->formulir_id)->delete();
        Inventory::where('formulir_id', $expedition_order_group_item->group->formulir_id)->delete();
        ExpeditionOrderGroup::where('formulir_id', $expedition_order_group_item->group->formulir_id)->delete();

        return [
            'list_expedition_order' => $list_expedition_order,
            'purchase_order' => $purchase_order
        ];
    }

    public static function fixSeederExpedition($invoice, $goods_received, $list_expedition_order, $purchase_order)
    {
        /**
         * Fix seeder
         * - Seeder Expedition Reference
         * - Seeder Expedition Order
         */
        
        $expedition_order_reference = ExpeditionOrderReference::where('expedition_reference_id', $purchase_order->formulir_id)->first();
        $expedition_order_reference->type_of_tax = $invoice->type_of_tax;
        $expedition_order_reference->subtotal = $invoice->subtotal;
        $expedition_order_reference->discount = $invoice->discount;
        $expedition_order_reference->tax_base = $invoice->tax_base;
        $expedition_order_reference->tax = $invoice->tax;
        $expedition_order_reference->total = $invoice->total;
        $expedition_order_reference->save();

        foreach ($expedition_order_reference->items as $expedition_order_reference_item) {
            $invoice_item = $invoice->items->where('item_id', $expedition_order_reference_item->item_id)->first();
            $expedition_order_reference_item->price = $invoice_item->price;
            $expedition_order_reference_item->discount = $invoice_item->discount;
            $expedition_order_reference_item->save();
        }

        foreach ($list_expedition_order->get() as $expedition_orders) {
            $subtotal = 0;
            $expedition_order = ExpeditionOrder::find($expedition_orders->id);
            // dd($expedition_order);
            $expedition_order_items = ExpeditionOrderItem::where('point_expedition_order_id', $expedition_order->id)->get();
            foreach ($expedition_order_items as $expedition_order_item) {
                $invoice_item = $invoice->items->where('item_id', $expedition_order_item->item_id)->first();
                $expedition_order_item->price = $invoice_item->price;
                $expedition_order_item->discount = $invoice_item->discount;
                $expedition_order_item->save();

                $subtotal += $expedition_order_item->quantity * $expedition_order_item->price - $expedition_order_item->quantity * $expedition_order_item->price * $expedition_order_item->discount / 100;
            }

            $discount_value = $subtotal * $expedition_order->discount / 100;
            $tax_base = $subtotal - $discount_value;
            $tax = 0;

            if ($expedition_order->type_of_tax == 'exclude') {
                $tax = $tax_base * 10 / 100;
            }

            if ($expedition_order->type_of_tax == 'include') {
                $tax_base = $tax_base * 100 / 110;
                $tax = $tax_base * 10 / 100;
            }

            $expedition_order->expedition_fee = $subtotal;
            $expedition_order->tax_base = $tax_base;
            $expedition_order->tax = $tax;
            $expedition_order->total = $tax_base + $tax;
            $expedition_order->save();
        }
    }

    public static function rejournalExpeditionOrder($invoice, $goods_received, $list_expedition_order, $purchase_order)
    {
        $expedition_order_first = $list_expedition_order->first();

        $group = new ExpeditionOrderGroup;
        $group->formulir_id = $invoice->formulir->id;
        $group->save();

        $total_fee = 0;
        foreach ($list_expedition_order->get() as $expedition_order) {
            // $expedition_order = ExpeditionOrder::find($expedition_orders->id);
            // $expedition_order->is_finish = 1;
            // $expedition_order->save();

            $group_detail = new ExpeditionOrderGroupDetail;
            $group_detail->point_expedition_order_group_id = $group->id;
            $group_detail->point_expedition_order_id = $expedition_order->id;
            $group_detail->save();

            $tax_base = $expedition_order->tax_base;
            $total = $expedition_order->total;

            $total_fee += $tax_base;

            // Journal Account Payable Expedition
            $account_payable_expedition = JournalHelper::getAccount('point expedition', 'account payable - expedition');
            $position = JournalHelper::position($account_payable_expedition);
            $journal = new Journal;
            $journal->form_date = $group->formulir->form_date;
            $journal->coa_id = $account_payable_expedition;
            $journal->description = 'purchase invoice "' . $group->formulir->form_number . '"';
            $journal->$position = $total;
            $journal->form_journal_id = $group->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $expedition_order->expedition_id;
            $journal->subledger_type = get_class(new Person);
            $journal->save();
            \Log::info('utang exp '. $position.' '. $total);

            // Journal Income Tax Expedition
            if ($expedition_order->tax != 0) {
                $income_tax_payable = JournalHelper::getAccount('point expedition', 'income tax receivable');
                $position = JournalHelper::position($income_tax_payable);
                $journal = new Journal();
                $journal->form_date = $group->formulir->form_date;
                $journal->coa_id = $income_tax_payable;
                $journal->description = 'purchase invoice "' . $group->formulir->form_number . '"';
                $journal->$position = $expedition_order->tax;
                $journal->form_journal_id = $group->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();

                \Log::info('tax expedition_order '. $position.' '. $expedition_order->tax);
            }
        }

        $continue = false;
        // $total_quantity_expedition = ExpeditionOrderItem::where('point_expedition_order_id', $list_expedition_order->first()->id)->selectRaw('sum(quantity) as quantity')->first()->quantity; 
        $subtotal_reference = 0;
        $list_expedition_order_tmp = $list_expedition_order->get();
        $expedition_order = ExpeditionOrder::find($list_expedition_order->first()->id);
        foreach ($expedition_order->items as $expedition_order_item) {
            // Journal Inventory
            $item_purchase_per_row = 0;
            $item_expedition_per_row = 0;

            $item_purchase_per_row = $expedition_order_item->quantity * $expedition_order_item->price - $expedition_order_item->quantity * $expedition_order_item->price * $expedition_order_item->discount / 100;
            if ($invoice->discount) {
                $discounty = $item_purchase_per_row * $invoice->discount / 100;
                $item_purchase_per_row = $item_purchase_per_row - $discounty;
            }

            if ($invoice->type_of_tax == 'include') {
                $item_purchase_per_row = $item_purchase_per_row * 100 / 110;
            }
            \Log::info('purchase per row ' .$item_purchase_per_row);
            $subtotal_reference += $expedition_order_item->quantity * $expedition_order_item->price - $expedition_order_item->quantity * $expedition_order_item->price * $expedition_order_item->discount / 100;
            foreach ($list_expedition_order_tmp as $expedition_order) {
                $total_quantity_expedition = ExpeditionOrderItem::where('point_expedition_order_id', $expedition_order->id)->selectRaw('sum(quantity) as quantity')->first()->quantity; 
                $item_expedition_per_row += $expedition_order_item->quantity * $expedition_order->tax_base / $total_quantity_expedition;
                \Log::info('exp per row ' .$item_expedition_per_row);
            }


            $position = JournalHelper::position($expedition_order_item->item->account_asset_id);
            $journal = new Journal();
            $journal->form_date = $group->formulir->form_date;
            $journal->coa_id = $expedition_order_item->item->account_asset_id;
            $journal->description = 'purchase invoice [' . $group->formulir->form_number.']';
            $journal->$position = $item_purchase_per_row + $item_expedition_per_row;
            $journal->form_journal_id = $group->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $expedition_order_item->item_id;
            $journal->subledger_type = get_class($expedition_order_item->item);
            $journal->save();
            \Log::info('sediaan '. $position.' '. $journal->$position);

            $warehouse = Warehouse::where('name', 'in transit')->first();
            if (!$warehouse) {
                $warehouse = ExpeditionOrderHelper::createWarehouse();
            }
            if (! $continue) {
                $inventory = new Inventory();
                $inventory->formulir_id = $group->formulir->id;
                $inventory->item_id = $expedition_order_item->item_id;
                $inventory->quantity = $expedition_order_item->quantity * $expedition_order_item->converter;
                $inventory->price = $expedition_order_item->price / $expedition_order_item->converter;
                $inventory->form_date = $group->formulir->form_date;
                $inventory->warehouse_id = $warehouse->id;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();

                $available_quantity = ExpeditionOrderHelper::availableQuantity($list_expedition_order->first()->form_reference_id, $expedition_order_item->item_id);
                $is_finish = $available_quantity == 0 ? true : false;
            }
        }

        /**
         * ACCOUNT PAYABLE REFERENCE
         */ 
        
        $reference_discount_value = $subtotal_reference * $invoice->discount / 100;
        $reference_tax_base = $subtotal_reference - $reference_discount_value;
        $reference_tax = 0;

        if ($invoice->type_of_tax == 'exclude') {
            $reference_tax = $reference_tax_base * 10 / 100;
        }
        if ($invoice->type_of_tax == 'include') {
            $reference_tax_base = $reference_tax_base * 100 / 110;
            $reference_tax = $reference_tax_base * 10 / 100;
        }

        $reference_total = $reference_tax + $reference_tax_base;
        // Journal Account Payable Purchasing
        $account_receiveable = JournalHelper::getAccount('point purchasing', 'account payable');
        $position = JournalHelper::position($account_receiveable);
        $journal = new Journal;
        $journal->form_date = $group->formulir->form_date;
        $journal->coa_id = $account_receiveable;
        $journal->description = 'purchase invoice [' . $group->formulir->form_number.']';
        $journal->$position = $reference_total;
        $journal->form_journal_id = $group->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id = $invoice->supplier_id;
        $journal->subledger_type = get_class(new Person);
        $journal->save();
        \Log::info('utang purchasing '. $position. ' ' .$journal->$position);

        if ($invoice->tax > 0) {
            $income_tax_receiveable = JournalHelper::getAccount('point purchasing', 'income tax receivable');
            $position = JournalHelper::position($income_tax_receiveable);
            $journal = new Journal;
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = $income_tax_receiveable;
            $journal->description = 'purchase invoice [' . $invoice->formulir->form_number.']';
            $journal->$position = $reference_tax;
            $journal->form_journal_id = $group->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            \Log::info('tax purchase '. $position.' ' .$journal->$position);

        }

        JournalHelper::checkJournalBalance($group->formulir_id);

        // update expedition reference 
        if ($is_finish) {
            $expedition_reference = ExpeditionOrderReference::where('expedition_reference_id', $expedition_order_first->form_reference_id)->first();
            $expedition_reference->finish = 1;
            $expedition_reference->save();
        }

        $journal = Journal::where('form_journal_id', $group->formulir_id)->get();
    }
}
