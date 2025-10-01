<?php

namespace Point\PointPurchasing\Helpers\FixedAssets;

use Illuminate\Http\Request;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\FixedAssetsContractReference;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\UserWarehouse;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoiceDetail;

class FixedAssetsInvoiceHelper
{
    public static function searchList($list_invoice, $status, $date_from, $date_to, $search)
    {
        $list_invoice = $list_invoice->where('form_status', '=', $status ? : 0);
        if ($date_from) {
            $list_invoice = $list_invoice->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_invoice = $list_invoice->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_invoice = $list_invoice->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_invoice;
    }

    public static function create(Request $request, $formulir, $references = null)
    {
        $invoice = new FixedAssetsInvoice;
        $invoice->formulir_id = $formulir->id;
        $invoice->supplier_id = $request->input('supplier_id');
        $invoice->due_date = date_format_db($request->input('due_date'), $request->input('time'));
        $invoice->expedition_fee = number_format_db($request->input('expedition_fee'));
        $invoice->save();

        $subtotal = 0;
        for ($i=0 ; $i < count($request->input('coa_id')) ; $i++) {
            $invoice_item = new FixedAssetsInvoiceDetail;
            $invoice_item->fixed_assets_invoice_id = $invoice->id;
            $invoice_item->coa_id = $request->input('coa_id')[$i];
            $invoice_item->name = $request->input('name')[$i];
            $invoice_item->allocation_id = $request->input('allocation_id')[$i];
            $invoice_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $invoice_item->price = number_format_db($request->input('item_price')[$i]);
            $invoice_item->discount = number_format_db($request->input('item_discount')[$i]) ? : 0;
            $invoice_item->unit = $request->input('item_unit')[$i];
            $invoice_item->save();

            // Insert to Allocation Report
            $amount = ($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price /100 * $invoice_item->discount);

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
            

            $subtotal += $amount;
        }

        if ($references != null) {
            foreach ($references as $reference) {
                formulir_lock($reference->formulir_id, $invoice->formulir_id);
                $reference->formulir->form_status = 1;
                $reference->formulir->save();
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
        $invoice->total = $tax_base + $tax + $invoice->expedition_fee;
        $invoice->save();

        foreach ($invoice->details as $invoice_detail) {
            // Journal inventory
            $total_per_row = $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
            if ($request->input('type_of_tax') == 'include') {
                $total_per_row = $total_per_row * 100 / 111;
            }

            $position = JournalHelper::position($invoice_detail->coa_id);
            $journal = new Journal();
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = $invoice_detail->coa_id;
            $journal->description = 'Goods Received Fixed Assets [' . $invoice->formulir->form_number.']';
            $journal->$position = $total_per_row;
            $journal->form_journal_id = $invoice->formulir_id;
            $journal->form_reference_id = $invoice_detail->id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            $coa = Coa::find($journal->coa_id);
            if ($coa->isFixedAssetAccount()) {
                $quantity = $invoice_detail->quantity;
                $price = $invoice_detail->price;
                $discount = $invoice_detail->discount;
                
                $contract_reference = new FixedAssetsContractReference();
                $contract_reference->form_reference_id = $invoice->formulir_id;
                $contract_reference->journal_id = $journal->id;
                $contract_reference->coa_id = $coa->id;
                $contract_reference->supplier_id = $invoice->supplier_id;
                $contract_reference->date_purchased = $invoice->formulir->form_date;
                $contract_reference->name = $invoice_detail->name;
                $contract_reference->unit = $invoice_detail->unit;
                $contract_reference->country = "";
                $contract_reference->total_paid = ($quantity * $price) - ($quantity * $price * $discount /  100);
                $contract_reference->depreciation = '0';
                $contract_reference->quantity = $quantity;
                $contract_reference->price = $price;
                $contract_reference->discount = $discount;
                $contract_reference->total_price = ($quantity * $price) - ($quantity * $price * $discount /  100);
                $contract_reference->useful_life = 0;
                $contract_reference->salvage_value = 0;
                $contract_reference->save();
            }
        }

        // Journal tax exclude and non-tax
        if ($request->input('type_of_tax') == 'exclude' || $request->input('type_of_tax') == 'non') {
            $data = array(
                'value_of_account_payable' => $total,
                'value_of_income_tax_receiveable' => $tax,
                'value_of_discount' => $discount * (-1),
                'value_of_expedition_cost' => $invoice->expedition_fee,
                'formulir' => $formulir,
                'invoice' => $invoice
            );
            self::journal($data);
        }

        // Journal tax include
        if ($request->input('type_of_tax') == 'include') {
            $data = array(
                'value_of_account_payable' => $total,
                'value_of_income_tax_receiveable' => $tax,
                'value_of_discount' => $discount,
                'value_of_expedition_cost' => $invoice->expedition_fee,
                'formulir' => $formulir,
                'invoice' => $invoice
            );
            self::journal($data);
        }

        return $invoice;
    }

    public static function journal($data)
    {
        // 1. Journal account receiveable
        $account_receiveable = JournalHelper::getAccount('point purchasing fixed assets', 'account payable');
        $position = JournalHelper::position($account_receiveable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_receiveable;
        $journal->description = 'Invoice Purchasing Fixed Assets[' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_payable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->supplier_id;
        $journal->subledger_type = get_class($data['invoice']->supplier);
        $journal->save();

        // 2. Journal income tax receiveable
        $income_tax_receiveable = JournalHelper::getAccount('point purchasing fixed assets', 'income tax receivable');
        $position = JournalHelper::position($income_tax_receiveable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $income_tax_receiveable;
        $journal->description = 'Invoice Purchasing Fixed Assets [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_income_tax_receiveable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->supplier_id;
        $journal->subledger_type = get_class($data['invoice']->supplier);
        $journal->save();

        // 3. Journal Purchase Discount
        if ($data['invoice']->discount > 0) {
            $purchasing_discount = JournalHelper::getAccount('point purchasing fixed assets', 'purchase discount');
            $position = JournalHelper::position($purchasing_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $purchasing_discount;
            $journal->description = 'Invoice Purchasing Fixed Assets [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->supplier_id;
            $journal->subledger_type = get_class($data['invoice']->supplier);
            $journal->save();
        }

        // 3. Journal Expedition Cost
        if ($data['invoice']->expedition_fee > 0) {
            $expedition = JournalHelper::getAccount('point purchasing fixed assets', 'expedition cost');
            $position = JournalHelper::position($expedition);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $expedition;
            $journal->description = 'Invoice Purchasing Fixed Assets [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_expedition_cost'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->supplier_id;
            $journal->subledger_type = get_class($data['invoice']->supplier);
            $journal->save();
        }
    }
}
