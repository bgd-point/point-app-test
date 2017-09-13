<?php

namespace Point\PointExpedition\Helpers;

use Illuminate\Http\Request;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Person;
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

    public static function create(Request $request, $formulir, $references)
    {
        $expedition = new Person;

        $invoice = new Invoice;
        $invoice->formulir_id = $formulir->id;
        $invoice->expedition_id = $request->input('expedition_id');
        $invoice->type_of_fee = '';
        $invoice->type_of_tax = $request->input('type_of_tax');
        $invoice->save();

        $subtotal = 0;
        for ($i = 0; $i < count($request->input('reference_item_type')); $i++) {
            $reference_item_type = $request->input('reference_item_type')[$i];
            $reference_item = $reference_item_type::find($request->input('reference_item_id')[$i]);

            $invoice_item = new InvoiceItem;
            $invoice_item->point_expedition_invoice_id = $invoice->id;
            $invoice_item->item_id = $reference_item->item_id;
            $invoice_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $invoice_item->item_fee = 0;
            $invoice_item->unit = $reference_item->unit;
            $invoice_item->save();

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

        foreach ($references as $reference) {
            formulir_lock($reference->formulir_id, $invoice->formulir_id);
            $reference->formulir->form_status = 1;
            $reference->formulir->save();
        }

        $subtotal += number_format_db($request->input('subtotal'));
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
         * JOURNAL INVOICE COST
         * --------------------------------------------------------------------------
         * COA CATEGORY     | ACCOUNT               | DEBIT         | CREDIT
         * --------------------------------------------------------------------------
         * CURRENT LIABILITY| ACCOUNT PAYABLE - EXP |               | xxxx
         * DIRECT EXPENSE   | EXPEDITION EXPENSE    | xxxx          |
         * LIABILITY        | INCOME TAX PAYABLE    | xxxx          |
         * CURRENT LIABILITY| EXPEDITION DISCOUNT   | xxxx          |
         * --------------------------------------------------------------------------
         **/
        
        /**
         * Payable = Expense - discount + ppn input
         */
        // 1. JOURNAL ACCOUNT PAYABLE - EXP
        $account_payable_expedition = JournalHelper::getAccount('point expedition', 'account payable - expedition');
        $position = JournalHelper::position($account_payable_expedition);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $account_payable_expedition;
        $journal->description = 'expedition invoice "' . $invoice->formulir->form_number . '"';
        $journal->$position = $invoice->total;
        $journal->form_journal_id = $invoice->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id = $invoice->expedition_id;
        $journal->subledger_type = get_class($expedition);
        $journal->save();

        $expedition_cost = 0;
        if ($invoice->type_of_tax == 'include') {
            $expedition_cost = $invoice->tax_base;
        } else {
            $expedition_cost = $invoice->subtotal;
        }

        // 2. JOURNAL EXPEDITION EXPENSE
        $account_payable_expedition = JournalHelper::getAccount('point expedition', 'expedition cost');
        $position = JournalHelper::position($account_payable_expedition);
        $journal = new Journal;
        $journal->form_date = $invoice->formulir->form_date;
        $journal->coa_id = $account_payable_expedition;
        $journal->description = 'expedition invoice "' . $invoice->formulir->form_number . '"';
        $journal->$position = $expedition_cost;
        $journal->form_journal_id = $invoice->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        // 3. JOURNAL INCOME TAX PAYABLE
        if ($request->input('tax') != 0) {
            $income_tax_payable = JournalHelper::getAccount('point expedition', 'income tax receivable');
            $position = JournalHelper::position($income_tax_payable);
            $journal = new Journal();
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = $income_tax_payable;
            $journal->description = 'expedition invoice "' . $invoice->formulir->form_number . '"';
            $journal->$position = $tax;
            $journal->form_journal_id = $invoice->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        // 4. JOURNAL EXPEDITION DISCOUNT
        if ($request->input('discount') != 0) {
            $expedition_discount_account = JournalHelper::getAccount('point expedition', 'expedition discount');
            $position = JournalHelper::position($expedition_discount_account);
            $journal = new Journal;
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = $expedition_discount_account;
            $journal->description = 'expedition invoice "' . $invoice->formulir->form_number . '"';
            $journal->$position = $discount_value * -1;
            $journal->form_journal_id = $invoice->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        $formulir->approval_status = 1;
        $formulir->save();

        JournalHelper::checkJournalBalance($formulir->id);

        return $invoice;
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
