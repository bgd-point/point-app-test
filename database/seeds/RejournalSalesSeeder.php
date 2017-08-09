<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\UserWarehouse;
use Point\PointSales\Models\Sales\Invoice;

class RejournalSalesSeeder extends Seeder
{
    public function run()
    {
    	\DB::beginTransaction();
        \Log::info('Rejournal seeder started');
        self::invoice();
        \Log::info('Rejournal seeder finished');
        \DB::commit();
    }

    public function invoice()
    {
        $list_invoice = Invoice::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->select('formulir.id')->get()->toArray();
        $journal = Journal::whereIn('form_journal_id', $list_invoice)->delete();
        $inventory = Inventory::whereIn('formulir_id', $list_invoice)->delete();
        $list_invoice = Invoice::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->selectOriginal()->get();
        \Log::info('Journal invoice indirect started');
        foreach ($list_invoice as $invoice) {
            $subtotal = 0;
            foreach ($invoice->items as $invoice_detail) {
                
                $total_per_row = $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
                $subtotal += $total_per_row;
                
                if ($invoice->type_of_tax == 'include') {
                    $total_per_row = $total_per_row * 100 / 110;
                }

                \Log::info('Journal inventory invoice '. $invoice->formulir->id);
                $item = Item::find($invoice_detail->item_id);
                $inventory = new Inventory();
                $inventory->formulir_id = $invoice->formulir->id;
                $inventory->item_id = $item->id;
                $inventory->quantity = $invoice_detail->quantity * $invoice_detail->converter;
                $inventory->price = $invoice_detail->price / $invoice_detail->converter;
                $inventory->form_date = $invoice->formulir->form_date;
                $inventory->warehouse_id = UserWarehouse::getWarehouse($invoice->formulir->created_by);

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->out();
            }

            $discount = $subtotal * $invoice->discount/100;
            $tax_base = $subtotal - $discount;
            $tax = 0;
            if ($invoice->type_of_tax == 'include') {
                $tax_base = $subtotal * 100 / 110;
                $tax = $subtotal * 10 / 100;
            } else if ($invoice->type_of_tax == 'exclude') {
                $tax = $subtotal * 10 / 100;
            }

            $invoice->subtotal = $subtotal;
            $invoice->discount = $invoice->discount;
            $invoice->tax_base = $tax_base;
            $invoice->tax = $tax;
            $invoice->type_of_tax = $invoice->type_of_tax;
            $invoice->total = $tax_base + $tax + $invoice->expedition_fee;
            $invoice->save();

            // Journal tax exclude and non-tax
            if ($invoice->type_of_tax == 'exclude' || $invoice->type_of_tax == 'non') {
                $data = array(
                    'value_of_account_receivable' => $invoice->total,
                    'value_of_income_tax_payable' => $tax,
                    'value_of_sale_of_goods' => $subtotal,
                    'value_of_discount' => $discount * (-1),
                    'value_of_expedition_income' => $invoice->expedition_fee,
                    'formulir' => $invoice->formulir,
                    'invoice' => $invoice
                );
                self::journal($data);
            } elseif ($invoice->type_of_tax == 'include') {
                $data = array(
                    'value_of_account_receivable' => $invoice->total,
                    'value_of_income_tax_payable' => $tax,
                    'value_of_sale_of_goods' => $tax_base,
                    'value_of_discount' => $discount,
                    'value_of_expedition_income' => $invoice->expedition_fee,
                    'formulir' => $invoice->formulir,
                    'invoice' => $invoice
                );
                self::journal($data);
            }

            JournalHelper::checkJournalBalance($invoice->formulir_id);
        }
    }

    public static function journal($data)
    {
        \Log::info('Journal Account Receivable');
        // 1. Journal Account Receivable
        $account_receivable = JournalHelper::getAccount('point sales indirect', 'account receivable');
        $position = JournalHelper::position($account_receivable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_receivable;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_receivable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        \Log::info('Journal Income Tax  Payable');
        // 2. Journal Income Tax  Payable
        if ($data['invoice']->tax != 0) {
            $income_tax_receivable = JournalHelper::getAccount('point sales indirect', 'income tax payable');
            $position = JournalHelper::position($income_tax_receivable);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $income_tax_receivable;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_income_tax_payable'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }
        
        \Log::info('Journal Sales Of Goods');
        // 3. Journal Sales Of Goods
        $sales_of_goods = JournalHelper::getAccount('point sales indirect', 'sale of goods');
        $position = JournalHelper::position($sales_of_goods);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $sales_of_goods;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_sale_of_goods'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        \Log::info('Journal Sales Discount');
        // 4. Journal Sales Discount
        if ($data['invoice']->discount > 0) {
            $sales_discount = JournalHelper::getAccount('point sales indirect', 'sales discount');
            $position = JournalHelper::position($sales_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $sales_discount;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        \Log::info('Journal Expedition Cost');
        // 5. Journal Expedition Cost
        if ($data['invoice']->expedition_fee > 0) {
            $cost_of_sales = JournalHelper::getAccount('point sales indirect', 'expedition income');
            $position = JournalHelper::position($cost_of_sales);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $cost_of_sales;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_expedition_income'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }
    }
}