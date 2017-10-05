<?php

namespace Point\PointExpedition\Helpers;

use Illuminate\Http\Request;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Helpers\ExpeditionOrderHelper;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderGroup;
use Point\PointExpedition\Models\ExpeditionOrderGroupDetail;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointExpedition\Models\Invoice;
use Point\PointExpedition\Models\InvoiceItem;
use Point\PointPurchasing\Helpers\InvoiceHelper as PurchasingInvoiceHelper;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;

class InvoiceHelper
{
    public static function searchList($list_invoice, $order_by, $order_type, $status, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_invoice = $list_invoice->where('formulir.form_status', '=', $status ? : 0);
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
                $q->where('formulir.form_number', 'like', '%' . $search . '%');
            });
        }

        return $list_invoice;
    }

    public static function create(Request $request, $formulir, $reference)
    {
        $expedition = new Person;

        $invoice = new Invoice;
        $invoice->formulir_id = $formulir->id;
        $invoice->expedition_id = $request->input('expedition_id');
        $invoice->type_of_fee = '';
        $invoice->type_of_tax = $request->input('type_of_tax');
        $invoice->save();

        for ($i = 0; $i < count($request->input('item_id')); $i++) {
            $invoice_item = new InvoiceItem;
            $invoice_item->point_expedition_invoice_id = $invoice->id;
            $invoice_item->item_id = $request->input('item_id')[$i];
            $invoice_item->quantity = number_format_db($request->input('quantity')[$i]);
            $invoice_item->discount = number_format_db($request->input('item_discount')[$i]);
            $invoice_item->price = number_format_db($request->input('price')[$i]);
            $invoice_item->item_fee = 0;
            $invoice_item->unit = $request->input('unit')[$i];
            $invoice_item->save();
        }

        $subtotal = number_format_db($request->input('subtotal'));

        formulir_lock($reference->formulir_id, $invoice->formulir_id);
        $reference->formulir->form_status = 1;
        $reference->formulir->save();

        $discount = $request->input('discount') ? number_format_db($request->input('discount')) : 0;
        $discount_value = $subtotal * $discount / 100;
        $tax_base = $subtotal - $discount_value;
        $tax = 0;

        if ($request->input('type_of_tax') == 'exclude') {
            $tax = $tax_base * 10 / 100;
        }
        if ($request->input('type_of_tax') == 'include') {
            $tax_base = $tax_base * 100 / 110;
            $tax = $tax_base * 10 / 100;
        }

        $invoice->subtotal = $subtotal;
        $invoice->discount = $discount;
        $invoice->tax_base = $tax_base;
        $invoice->tax = $tax;
        $invoice->total = $tax_base + $tax;
        $invoice->save();
        /**
         * If discount or tax was modified, journal again
         * 
         */
        if (($request->input('original_subtotal') != $invoice->expedition_fee) || ($request->input('original_discount') != $invoice->discount) || ($request->input('original_type_of_tax') != $invoice->type_of_tax)) {
            self::rejournal($invoice, $reference);
        }

        $formulir->approval_status = 1;
        $formulir->save();

        JournalHelper::checkJournalBalance($formulir->id);
        return $invoice;
    }

    public static function rejournal($invoice, $expedition_order)
    {
        /**
         * Reference = Expedition order
         * Process :
         * Remove journal Expedition order
         * Remove journal Invoice purchasing
         */
        $expedition_order_group_item = ExpeditionOrderGroupDetail::where('point_expedition_order_id', $expedition_order->id)->first();
        $list_expedition_order = ExpeditionOrderGroupDetail::where('point_expedition_order_group_id', $expedition_order_group_item->point_expedition_order_group_id)->select('point_expedition_order_id')->get()->toArray();
        
        ExpeditionOrderHelper::removeJournal($expedition_order);
        $list_goods_received = FormulirLock::join('formulir', 'formulir.id', '=', 'formulir_lock.locking_id')
            ->where('locked_id', $expedition_order->formulir_id)
            ->where('formulir.formulirable_type', get_class(new GoodsReceived))
            ->whereIn('formulir.form_status', [1, 0])
            ->where('formulir.approval_status', 1)
            ->whereNotNull('formulir.form_number')
            ->select('formulir.*');
        $formulir = $list_goods_received->first();
        $goods_received = '';
        if ($formulir) {
            $goods_received = $formulir->formulirable_type::find($formulir->formulirable_id);
            PurchasingInvoiceHelper::removeJournalExpeditionOrder($goods_received);    
        }
        
        self::journalInvoice($invoice, $expedition_order, $goods_received, $list_expedition_order);
    }

    public static function journalInvoice($invoice, $expedition_order_ref, $goods_received, $list_expedition_order)
    {
        $list_expedition_order = ExpeditionOrder::whereIn('id', $list_expedition_order)->get();

        $group = new ExpeditionOrderGroup;
        $group->formulir_id = $invoice->formulir->id;
        $group->save();

        $total_fee = 0;
        foreach ($list_expedition_order as $expedition_order) {
            $expedition_order->is_finish = 1;
            $expedition_order->save();
            $ref = $expedition_order;
            if ($expedition_order->id == $expedition_order_ref->id) {
                $ref = $invoice;
            }

            $group_detail = new ExpeditionOrderGroupDetail;
            $group_detail->point_expedition_order_group_id = $group->id;
            $group_detail->point_expedition_order_id = $expedition_order->id;
            $group_detail->save();
            $tax_base = $ref->tax_base;
            $total = $ref->total;

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
            if ($ref->tax != 0) {
                $income_tax_payable = JournalHelper::getAccount('point expedition', 'income tax receivable');
                $position = JournalHelper::position($income_tax_payable);
                $journal = new Journal();
                $journal->form_date = $group->formulir->form_date;
                $journal->coa_id = $income_tax_payable;
                $journal->description = 'purchase invoice "' . $group->formulir->form_number . '"';
                $journal->$position = $ref->tax;
                $journal->form_journal_id = $group->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();

                \Log::info('tax expedition_order '. $position.' '. $ref->tax);
            }
        }

        $form_reference = Formulir::find($expedition_order->form_reference_id);
        $reference = $form_reference->formulirable_type::find($form_reference->formulirable_id);
        if (! $reference->supplier_id) {
            $reference->person_id = $reference->person_id;
        } else{
            $reference->person_id = $reference->supplier_id;
        }

        $continue = false;
        // $total_quantity_expedition = ExpeditionOrderItem::where('point_expedition_order_id', $list_expedition_order->first()->id)->selectRaw('sum(quantity) as quantity')->first()->quantity; 
        $subtotal_reference = 0;
        $list_expedition_order_tmp = $list_expedition_order;
        foreach ($expedition_order->items as $expedition_order_item) {
            // Journal Inventory
            $item_purchase_per_row = 0;
            $item_expedition_per_row = 0;

            $item_purchase_per_row = $expedition_order_item->quantity * $expedition_order_item->price - $expedition_order_item->quantity * $expedition_order_item->price * $expedition_order_item->discount / 100;
            if ($reference->discount) {
                $discounty = $item_purchase_per_row * $reference->discount / 100;
                $item_purchase_per_row = $item_purchase_per_row - $discounty;
            }

            if ($reference->type_of_tax == 'include') {
                $item_purchase_per_row = $item_purchase_per_row * 100 / 110;
            }
            
            \Log::info('purchase per row ' .$item_purchase_per_row);
            $subtotal_reference += $expedition_order_item->quantity * $expedition_order_item->price - $expedition_order_item->quantity * $expedition_order_item->price * $expedition_order_item->discount / 100;
            foreach ($list_expedition_order_tmp as $expedition_order) {
                $ref = $expedition_order;
                if ($expedition_order->id == $expedition_order_ref->id) {
                    $ref = $invoice;
                }
                
                
                $total_quantity_expedition = ExpeditionOrderItem::where('point_expedition_order_id', $expedition_order->id)->selectRaw('sum(quantity) as quantity')->first()->quantity; 
                $item_expedition_per_row += $expedition_order_item->quantity * $ref->tax_base / $total_quantity_expedition;
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
            if ($goods_received) {
                $warehouse = $goods_received->warehouse;
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
        
        $reference_discount_value = $subtotal_reference * $reference->discount / 100;
        $reference_tax_base = $subtotal_reference - $reference_discount_value;
        $reference_tax = 0;

        if ($reference->type_of_tax == 'exclude') {
            $reference_tax = $reference_tax_base * 10 / 100;
        }
        if ($reference->type_of_tax == 'include') {
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
        $journal->subledger_id = $reference->person_id;
        $journal->subledger_type = get_class(new Person);
        $journal->save();

        \Log::info('utang purchasing '. $position. ' ' .$journal->$position);
        if ($reference->tax > 0) {
            $income_tax_receiveable = JournalHelper::getAccount('point purchasing', 'income tax receivable');
            $position = JournalHelper::position($income_tax_receiveable);
            $journal = new Journal;
            $journal->form_date = $reference->formulir->form_date;
            $journal->coa_id = $income_tax_receiveable;
            $journal->description = 'purchase invoice [' . $group->formulir->form_number.']';
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
            $expedition_reference = ExpeditionOrderReference::where('expedition_reference_id', $expedition_order->form_reference_id)->first();
            $expedition_reference->finish = 1;
            $expedition_reference->save();
        }

        $journal = Journal::where('form_journal_id', $group->formulir_id)->get();
        
    }

    public static function journalDifferences($invoice, $expedition_order_id, $original_fee, $expedition_fee)
    {
        // ------------------------------------------------------------------
        // RETURN ACCOUNT PAYABLE FROM EXPEDITION ORDER
        // ------------------------------------------------------------------
        $journal_reference_expedition_order = ExpeditionOrderGroupDetail::where('point_expedition_order_id', $expedition_order_id)->first();
        $expedition_order = ExpeditionOrder::find($expedition_order_id);
        
        $account_payable_expedition = JournalHelper::getAccount('point expedition', 'account payable - expedition');
        $position = JournalHelper::position($account_payable_expedition);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $account_payable_expedition;
        $journal->description = 'expedition order "' . $invoice->formulir->form_number . '"';
        $journal->$position = $original_fee * -1;
        $journal->form_journal_id = $invoice->formulir_id;
        $journal->form_reference_id = $journal_reference_expedition_order->group->formulir_id;
        $journal->subledger_id = $expedition_order->expedition_id;
        $journal->subledger_type = get_class(new Person);
        $journal->save();

        $expedition_cost = JournalHelper::getAccount('point expedition', 'expedition cost');
        $position = JournalHelper::position($expedition_cost);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $expedition_cost;
        $journal->description = 'expedition order "' . $invoice->formulir->form_number . '"';
        $journal->$position = $original_fee  * -1;
        $journal->form_journal_id = $invoice->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        // ------------------------------------------------------------------
        // JOURNAL 
        // 1. EXPEDITION COST
        // 2. EXPEDITION PAYABLE
        // ------------------------------------------------------------------

        $account_payable_expedition = JournalHelper::getAccount('point expedition', 'account payable - expedition');
        $position = JournalHelper::position($account_payable_expedition);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $account_payable_expedition;
        $journal->description = 'expedition order "' . $invoice->formulir->form_number . '"';
        $journal->$position = $expedition_fee;
        $journal->form_journal_id = $invoice->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id = $expedition_order->expedition_id;
        $journal->subledger_type = get_class(new Person);
        $journal->save();
        \Log::info('- exp '. $position.' '. $journal->$position);

        $expedition_cost = JournalHelper::getAccount('point expedition', 'expedition cost');
        $position = JournalHelper::position($expedition_cost);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $expedition_cost;
        $journal->description = 'expedition order "' . $invoice->formulir->form_number . '"';
        $journal->$position = $expedition_fee;
        $journal->form_journal_id = $invoice->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
        \Log::info('- exp cost '. $position.' '. $original_fee);
    }

    public static function storeBasicInvoice(Request $request, $formulir)
    {
        $invoice = new Invoice;
        $invoice->formulir_id = $formulir->id;
        $invoice->expedition_id = $request->input('expedition_id');
        $invoice->total = number_format_db($request->input('total'));
        $invoice->save();

        /**
         * JOURNAL INVOICE COST
         * --------------------------------------------------------------------------
         * COA CATEGORY          | ACCOUNT               | DEBIT         | CREDIT
         * --------------------------------------------------------------------------
         * #1. CURRENT LIABILITY | ACCOUNT PAYABLE - EXP |               | xxxx
         * #2. DIRECT EXPENSE    | EXPEDITION EXPENSE    | xxxx          |
         * --------------------------------------------------------------------------
         **/

        // JOURNAL #1 of #2 - ACCOUNT PAYABLE - EXPEDITION
        $expedition = new Person;

        $account_payable_expedition = Coa::where('name', 'Account Payable - Expedition')->first();
        $position = JournalHelper::position($account_payable_expedition->id);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $account_payable_expedition->id;
        $journal->description = 'invoice expedition "' . $invoice->formulir->form_number . '"';
        $journal->$position = $invoice->total;
        $journal->form_journal_id = $invoice->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id = $invoice->expedition_id;
        $journal->subledger_type = get_class($expedition);
        $journal->save();

        # JOURNAL #2 of #2 EXPEDITION EXPENSE
        $account_payable_expedition = Coa::where('name', 'Expedition Cost')->first();
        $position = JournalHelper::position($account_payable_expedition->id);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $account_payable_expedition->id;
        $journal->description = 'invoice expedition "' . $invoice->formulir->form_number . '"';
        $journal->$position = $invoice->total;
        $journal->form_journal_id = $invoice->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        $formulir->approval_status = 1;
        $formulir->save();
        
        JournalHelper::checkJournalBalance($formulir->id);

        return $invoice;
    }
}
