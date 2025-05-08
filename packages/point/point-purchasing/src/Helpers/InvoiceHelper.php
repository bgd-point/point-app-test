<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\UserWarehouse;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\Invoice;
use Point\PointPurchasing\Models\Inventory\InvoiceItem;

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

    public static function create(Request $request, $formulir, $references = null)
    {
        $dc = new \stdClass();
        $dc->debit = 0;
        $dc->credit = 0;

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
            AllocationHelper::save($invoice->formulir_id, $invoice_item->allocation_id, $amount * -1, $formulir->notes);

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
                Formulir::where('id', $reference->formulir_id)->update([
                    'form_status' => 1
                ]);
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

        foreach ($invoice->items as $invoice_detail) {
            $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
            $goods_received_item = ReferHelper::getReferBy(get_class($invoice_detail), $invoice_detail->id, get_class($invoice), $invoice->id);
            if ($goods_received_item) {
                $goods_received = GoodsReceived::find($goods_received_item->point_purchasing_goods_received_id);
                $warehouse_id = $goods_received->warehouse_id;
            }
 
            // Journal inventory
            $total_per_row = $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
            if ($invoice->discount) {
                $discounty = $total_per_row * $discount / $subtotal;
                $total_per_row = $total_per_row - $discounty;
            }
            if ($request->input('type_of_tax') == 'include') {
                $total_per_row = $total_per_row * 100 / 111;
            }

            $position = JournalHelper::position($invoice_detail->item->account_asset_id);

            $journal = new Journal();
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = $invoice_detail->item->account_asset_id;
            $journal->description = 'Goods Received [' . $invoice->formulir->form_number.']';
            $journal->$position = $total_per_row;
            $journal->form_journal_id = $invoice->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $invoice_detail->item_id;
            $journal->subledger_type = get_class($invoice_detail->item);
            $journal->save();

            $dc->debit += $total_per_row;

            // insert new inventory
            $item = Item::find($invoice_detail->item_id);
            $inventory = new Inventory();
            $inventory->formulir_id = $formulir->id;
            $inventory->item_id = $item->id;
            $inventory->quantity = $invoice_detail->quantity * $invoice_detail->converter;
            $inventory->price = $invoice_detail->price / $invoice_detail->converter;
            $inventory->form_date = $formulir->form_date;
            $inventory->warehouse_id = $warehouse_id;

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->in();
        }

        // Journal tax exclude and non-tax
        if ($request->input('type_of_tax') == 'exclude' || $request->input('type_of_tax') == 'non') {
            $data = array(
                'value_of_account_payable' => $total,
                'value_of_income_tax_receiveable' => $tax,
                'value_of_expedition_cost' => $invoice->expedition_fee,
                'formulir' => $formulir,
                'invoice' => $invoice
            );
            $dc2 = self::journal($data);
            $dc->debit += $dc2->debit;
            $dc->credit += $dc2->credit;
        } elseif ($request->input('type_of_tax') == 'include') {
            $data = array(
                'value_of_account_payable' => $total,
                'value_of_income_tax_receiveable' => $tax,
                'value_of_expedition_cost' => $invoice->expedition_fee,
                'formulir' => $formulir,
                'invoice' => $invoice
            );
            $dc2 = self::journal($data);
            $dc->debit += $dc2->debit;
            $dc->credit += $dc2->credit;
        }

        
        \Log::info('dc ' . $dc->debit . ' != ' . $dc->credit .' '. $invoice->formulir->form_date.' '.$invoice->formulir_id);
        if($dc->debit !== $dc->credit) {
            $journal = new Journal();
            $journal->form_date = $invoice->formulir->form_date;
            $journal->coa_id = 151;
            $journal->description = 'Selisih pembulatan';
            $journal->debit = $dc->credit - $dc->debit;
            $journal->form_journal_id = $invoice->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        JournalHelper::checkJournalBalance($invoice->formulir_id);
        return $invoice;
    }

    public static function journal($data)
    {
        $dc = new \stdClass();
        $dc->debit = 0;
        $dc->credit = 0;

        // 1. Journal account receiveable
        $account_receiveable = JournalHelper::getAccount('point purchasing', 'account payable');
        $position = JournalHelper::position($account_receiveable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_receiveable;
        $journal->description = 'Invoice Purchasing [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_payable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->supplier_id;
        $journal->subledger_type = get_class($data['invoice']->supplier);
        $journal->save();
        
        $dc->credit += $data['value_of_account_payable'];

        // 2. Journal income tax receiveable
        $income_tax_receiveable = JournalHelper::getAccount('point purchasing', 'income tax receivable');
        $position = JournalHelper::position($income_tax_receiveable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $income_tax_receiveable;
        $journal->description = 'Invoice Purchasing [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_income_tax_receiveable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
        $dc->debit += $data['value_of_income_tax_receiveable'];

        // 3. Journal Expedition Cost
        if ($data['invoice']->expedition_fee > 0) {
            $expedition = JournalHelper::getAccount('point purchasing', 'expedition cost');
            $position = JournalHelper::position($expedition);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $expedition;
            $journal->description = 'Invoice Purchasing [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_expedition_cost'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            $dc->debit += $data['value_of_expedition_cost'];
        }
        return $dc;
    }
}
