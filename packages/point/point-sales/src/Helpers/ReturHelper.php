<?php

namespace Point\PointSales\Helpers;

use Illuminate\Http\Request;
use Point\Core\Models\Vesa;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\AccountPayableAndReceivableDetail;
use Point\Framework\Models\Journal;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\Retur;
use Point\PointSales\Models\Sales\ReturItem;

class ReturHelper
{
    public static function searchList($list_retur, $date_from, $date_to, $search)
    {
        if ($date_from) {
            $list_retur = $list_retur->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_retur = $list_retur->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_retur = $list_retur->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                    ->orWhere('person.code', 'like', '%'.$search.'%')
                    ->orWhere('person.name', 'like', '%'.$search.'%');
            });
        }

        return $list_retur;
    }

    public static function create(Request $request, $formulir)
    {
        $retur = new Retur;
        $retur->formulir_id = $formulir->id;
        $retur->person_id = $request->input('person_id');
        $retur->expedition_fee = number_format_db($request->input('expedition_fee'));
        $retur->save();

        $subtotal = 0;

        for ($i=0 ; $i < count($request->input('reference_item_type')) ; $i++) {
            $reference_item_type = $request->input('reference_item_type')[$i];
            $reference_item = $reference_item_type::find($request->input('reference_item_id')[$i]);

            $retur_item = new ReturItem;
            $retur_item->point_sales_retur_id = $retur->id;
            $retur_item->item_id = $reference_item->item_id;
            $retur_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $retur_item->price = number_format_db($request->input('item_price')[$i]);
            $retur_item->discount = number_format_db($request->input('item_discount')[$i]);
            $retur_item->unit = $reference_item->unit;
            $retur_item->converter = $reference_item->converter;
            $retur_item->save();

            ReferHelper::create(
                $request->input('reference_item_type')[$i],
                $request->input('reference_item_id')[$i],
                get_class($retur_item),
                $retur_item->id,
                get_class($retur),
                $retur->id,
                $retur_item->quantity
            );

            $subtotal += ($retur_item->quantity * $retur_item->price) - ($retur_item->quantity * $retur_item->price /100 * $retur_item->discount);
        }

        $invoice = Invoice::find($request->input('invoice_id'));
        formulir_lock($invoice->formulir_id, $retur->formulir_id);

        $discount = number_format_db($request->input('discount'));
        $tax_base = $subtotal - $discount;
        $tax = 0;

        if ($request->input('type_of_tax') == 'exclude') {
            $tax = $tax_base * 10 / 100;
        }
        if ($request->input('type_of_tax') == 'include') {
            $tax_base =  $tax_base * 100 / 110;
            $tax =  $tax_base * 10 / 100;
        }

        $retur->subtotal = $subtotal;
        $retur->discount = $discount;
        $retur->tax_base = $tax_base;
        $retur->tax = $tax;
        $retur->total = $tax_base + $tax + $retur->expedition_fee;
        $retur->save();

        $invoice = Invoice::find($retur->point_sales_invoice_id);
        $apr = AccountPayableAndReceivable::where('formulir_reference_id', $invoice->formulir_id)->first();

        $account_payable_and_receivable_detail = new AccountPayableAndReceivableDetail;
        $account_payable_and_receivable_detail->account_payable_and_receivable_id = $apr->id;
        $account_payable_and_receivable_detail->formulir_reference_id = $retur->formulir->id;
        $account_payable_and_receivable_detail->amount = $retur->total;
        $account_payable_and_receivable_detail->form_date = $retur->formulir->form_date;
        $account_payable_and_receivable_detail->notes = 'RETUR';
        $account_payable_and_receivable_detail->save();
        return $retur;
    }

    public static function journal($retur)
    {
        /**
         * COA CATEGORY         | ACCOUNT                                  | DEBIT         | CREDIT
         * ------------------------------------------------------------------------------------------
         * INVENTORIES          | $return_item->item_id                    |  xxxx         |
         * ACCOUNT RECEIVABLE   | Account Receivable - Sales               |               | xxxx
         * ------------------------------------------------------------------------------------------
         */

        foreach ($retur->items as $retur_detail) {
            
            // JOURNAL #1 of #2
            $position = JournalHelper::position($retur_detail->item->account_asset_id);
            $journal = new Journal();
            $journal->form_date = $retur->formulir->form_date;
            $journal->coa_id = $retur_detail->item->account_asset_id;
            $journal->description = 'Return [' . $retur->formulir->form_number.']';
            $journal->$position = $retur_detail->quantity * $retur_detail->price - $retur_detail->quantity * $retur_detail->price/100 * $retur_detail->discount ;
            $journal->form_journal_id = $retur->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $retur_detail->item_id;
            $journal->subledger_type = get_class($retur_detail->item);
            $journal->save();
        }

        // JOURNAL #2 of #2
        $default_account = JournalHelper::getAccount('point sales indirect', 'account receivable');
        $position = 'credit';
        $journal = new Journal();
        $journal->form_date = $retur->formulir->form_date;
        $journal->coa_id = $default_account;
        $journal->description = 'Return [' . $retur->formulir->form_number.']';
        $journal->$position = $retur->subtotal;
        $journal->form_journal_id = $retur->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id = $retur->person_id;
        $journal->subledger_type = get_class($retur->person);
        $journal->save();

        /**
         * COA CATEGORY         | ACCOUNT                                   | DEBIT         | CREDIT
         * ------------------------------------------------------------------------------------------
         * direct expenses      | cost of sale                              | xxxx          |
         * INVENTORIES          | invoice_detail_item->item_id              |               | xxxx
         * ------------------------------------------------------------------------------------------
         */

        foreach ($retur->items as $retur_detail) {
            // JOURNAL #1 of #2
            $default_account = JournalHelper::getAccount('point sales indirect', 'cost of sales');
            $position = 'debit';
            $journal = new Journal();
            $journal->form_date = $retur->formulir->form_date;
            $journal->coa_id = $default_account;
            $journal->description = 'Return [' . $retur->formulir->form_number.']';
            $journal->$position = $retur_detail->quantity * $retur_detail->price - $retur_detail->quantity * $retur_detail->price/100 * $retur_detail->discount ;
            $journal->form_journal_id = $retur->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
            
            // JOURNAL #2 of #2
            $position = 'credit';
            $journal = new Journal();
            $journal->form_date = $retur->formulir->form_date;
            $journal->coa_id = $retur_detail->item->account_asset_id;
            $journal->description = 'Return [' . $retur->formulir->form_number.']';
            $journal->$position = $retur_detail->quantity * $retur_detail->price - $retur_detail->quantity * $retur_detail->price/100 * $retur_detail->discount ;
            $journal->form_journal_id = $retur->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $retur_detail->item_id;
            $journal->subledger_type = get_class($retur_detail->item);
            $journal->save();
        }
    }
}
