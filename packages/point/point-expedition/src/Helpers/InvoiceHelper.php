<?php

namespace Point\PointExpedition\Helpers;

use Illuminate\Http\Request;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;
use Point\PointExpedition\Helpers\ExpeditionOrderHelper;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderGroupDetail;
use Point\PointExpedition\Models\Invoice;
use Point\PointExpedition\Models\InvoiceItem;

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

        $subtotal = 0;
        for ($i = 0; $i < count($request->input('item_id')); $i++) {
            $invoice_item = new InvoiceItem;
            $invoice_item->point_expedition_invoice_id = $invoice->id;
            $invoice_item->item_id = $request->input('item_id')[$i];
            $invoice_item->quantity = number_format_db($request->input('quantity')[$i]);
            $invoice_item->discount = number_format_db($request->input('item_discount')[$i]);
            $invoice_item->item_fee = 0;
            $invoice_item->unit = $request->input('unit')[$i];
            $invoice_item->save();

            $subtotal += ($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price * $invoice_item->discount / 100);
        }

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
        if (($request->input('original_discount') != $invoice->discount) || ($request->input('original_type_of_tax') != $invoice->type_of_tax)) {
            self::rejournal($invoice, $reference);
        }

        $formulir->approval_status = 1;
        $formulir->save();

        JournalHelper::checkJournalBalance($formulir->id);
        return $invoice;
    }

    public static function rejournal($invoice, $reference)
    {
        /**
         * Reference = Expedition order
         * Process :
         * Remove journal Expedition order
         * Remove journal Goods received
         * Remove journal Invoice purchasing
         */
        
        ExpeditionOrderHelper::removeJournal($reference);
        
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
