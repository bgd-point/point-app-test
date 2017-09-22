<?php

use Illuminate\Database\Seeder;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointSales\Models\Pos\Pos;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Service\Invoice as ServiceInvoice;

class RejournalSalesSeeder extends Seeder
{
    public function run()
    {
    	\DB::beginTransaction();
        \Log::info('---- Seeder Sales invoice starting ----');
        self::invoice();
        \Log::info('---- Seeder Sales invoice finished ----');
        \Log::info('---- Seeder Sales service invoice starting ----');
        self::invoiceService();
        \Log::info('---- Seeder Sales service invoice finished ----');
        \Log::info('---- Seeder POS starting ----');
        self::pos();
        \Log::info('---- Seeder POS starting ----');

        \DB::commit();
    }

    public function invoice()
    {
        $list_invoice = Invoice::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->select('formulir.id')->get()->toArray();
        $journal = Journal::whereIn('form_journal_id', $list_invoice)->delete();
        $account_receivable = AccountPayableAndReceivable::whereIn('formulir_reference_id', $list_invoice)->delete();
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

    public function invoiceService()
    {
        $list_invoice = ServiceInvoice::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->select('formulir.id')->get()->toArray();
        $journal = Journal::whereIn('form_journal_id', $list_invoice)->delete();
        $account_receivable = AccountPayableAndReceivable::whereIn('formulir_reference_id', $list_invoice)->delete();
        $inventory = Inventory::whereIn('formulir_id', $list_invoice)->delete();
        $list_invoice = ServiceInvoice::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->selectOriginal()->get();
        \Log::info('Journal invoice service started');
        foreach ($list_invoice as $invoice) {
            $subtotal_service = 0;
            $subtotal_item = 0;

            foreach ($invoice->items as $invoice_detail) {
                $total_per_row = $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
                $subtotal_item += $total_per_row;
            }

            foreach ($invoice->services as $invoice_detail) {
                $total_per_row = $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
                $subtotal_service += $total_per_row;
            }

            $subtotal = $subtotal_item + $subtotal_service;
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
            $invoice->total = $tax_base + $tax;
            $invoice->save();

            // Journal tax exclude and non-tax
            if ($invoice->type_of_tax == 'exclude' || $invoice->type_of_tax == 'non') {
                $data = array(
                    'value_of_account_receivable' => $invoice->total,
                    'value_of_income_tax_payable' => $tax,
                    'value_of_sale_of_goods' => $subtotal_item,
                    'value_of_discount' => $discount * (-1),
                    'value_cost_of_sales' => $subtotal_item,
                    'value_of_service_income' => $subtotal_service,
                    'formulir' => $invoice->formulir,
                    'invoice' => $invoice
                );
                self::journalService($data);
            } elseif ($invoice->type_of_tax == 'include') {
                $data = array(
                    'value_of_account_receivable' => $invoice->total,
                    'value_of_income_tax_payable' => $tax,
                    'value_of_sale_of_goods' => $subtotal_item,
                    'value_of_discount' => $discount,
                    'value_cost_of_sales' => $subtotal_item,
                    'value_of_service_income' => $subtotal_service,
                    'formulir' => $invoice->formulir,
                    'invoice' => $invoice
                );
                self::journalService($data);
            }

            JournalHelper::checkJournalBalance($invoice->formulir_id);
        }
    }

    public static function journalService($data)
    {
        \Log::info('Journal Account Receivable');
        // 1. Journal Account Receivable
        $account_receivable = JournalHelper::getAccount('point sales service', 'account receivable');
        $position = JournalHelper::position($account_receivable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_receivable;
        $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_receivable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        \Log::info('Journal Income Tax Payable');
        // 2. Journal Income Tax Payable
        if ($data['invoice']->tax != 0) {
            $income_tax_receivable = JournalHelper::getAccount('point sales service', 'income tax payable');
            $position = JournalHelper::position($income_tax_receivable);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $income_tax_receivable;
            $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_income_tax_payable'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        // 3. Journal Sales of Goods
        if($data['value_of_sale_of_goods'] > 0) {
            \Log::info('Journal Sales of Goods');
            $sales_of_goods = JournalHelper::getAccount('point sales service', 'sale of goods');
            $position = JournalHelper::position($sales_of_goods);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $sales_of_goods;
            $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_sale_of_goods'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        // 4. Journal Sales Discount
        if ($data['invoice']->discount > 0) {
            \Log::info('Journal Sales Discount');
            $sales_discount = JournalHelper::getAccount('point sales service', 'sales discount');
            $position = JournalHelper::position($sales_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $sales_discount;
            $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        \Log::info('Journal Sales Service Income');
        $service_income = JournalHelper::getAccount('point sales service', 'service income');
        $position = JournalHelper::position($service_income);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $service_income;
        $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_service_income'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        if($data['value_of_sale_of_goods'] > 0) {
            self::journalInventory($data);
        }
    }

    public static function journalInventory($data)
    {
        $warehouse_id = UserWarehouse::getWarehouse($data['invoice']->formulir->created_by);
        foreach ($data['invoice']->items as $invoice_item) {
            $quantity = $invoice_item->quantity;
            $price = $invoice_item->price;

            if ($quantity > 0) {
                \Log::info('Inventory Out');
                // inventory control
                $inventory = new Inventory;
                $inventory->formulir_id = $data['formulir']->id;
                $inventory->item_id = $invoice_item->item_id;
                $inventory->quantity = $quantity;
                $inventory->price = $price;
                $inventory->form_date = $data['formulir']->form_date;
                $inventory->warehouse_id = $warehouse_id;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->out();

                $cost = InventoryHelper::getCostOfSales(\Carbon::now(), $inventory->item_id, $warehouse_id) * $inventory->quantity;

                // JOURNAL #5 of #6 - INVENTORY
                \Log::info('Journal Inventory');
                
                $journal = new Journal;
                $journal->form_date = $data['formulir']->form_date;
                $journal->coa_id = $inventory->item->account_asset_id;
                $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
                $journal->credit = $cost;
                $journal->form_journal_id = $data['formulir']->id;
                $journal->form_reference_id;
                $journal->subledger_id = $inventory->item_id;
                $journal->subledger_type = get_class($inventory->item);
                $journal->save();

                \Log::info('Journal Cost of sales');
                // 5. Journal cost of sales
                $sales_discount = JournalHelper::getAccount('point sales service', 'cost of sales');
                $position = JournalHelper::position($sales_discount);
                $journal = new Journal;
                $journal->form_date = $data['formulir']->form_date;
                $journal->coa_id = $sales_discount;
                $journal->description = 'invoice service sales [' . $data['formulir']->form_number.']';
                $journal->$position = $cost;
                $journal->form_journal_id = $data['formulir']->id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }
        }
    }

    public function pos()
    {
        $formulir_pos = Pos::joinFormulir()->close()->notArchived()->select('formulir_id')->get()->toArray();
        $journal = Journal::whereIn('form_journal_id', $formulir_pos)->delete();
        $remove_inventory = Inventory::whereIn('formulir_id', $formulir_pos)->delete();
        $list_pos = Pos::whereIn('formulir_id', $formulir_pos)->get();
        foreach ($list_pos as $pos) {
            $cost_of_sales = self::costOfSales($pos);
            self::journalPos($cost_of_sales, $pos);
            JournalHelper::checkJournalBalance($pos->formulir_id);
        }
    }

    public static function costOfSales($pos)
    {
        $cost_of_sales = 0;
        foreach ($pos->items as $pos_item) {
            // inventory control
            $inventory = new Inventory;

            $inventory->formulir_id = $pos->formulir_id;
            $inventory->item_id = $pos_item->item_id;
            $inventory->quantity = $pos_item->quantity;
            $inventory->price = $pos_item->price;
            $inventory->form_date = $pos->formulir->form_date;
            $inventory->warehouse_id = $pos->warehouse_id;

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->out();

            $cost = InventoryHelper::getCostOfSales($pos->formulir->form_date, $inventory->item_id, $pos->warehouse_id) * abs($inventory->quantity);
            $cost_of_sales += $cost;

            // JOURNAL #5 of #6 - INVENTORY
            $journal = new Journal;
            $journal->form_date = $pos->formulir->form_date;
            $journal->coa_id = $inventory->item->account_asset_id;
            $journal->description = 'point of sales "' . $inventory->item->codeName.'"';
            $journal->credit = $cost;
            $journal->form_journal_id = $pos->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $inventory->item_id;
            $journal->subledger_type = get_class($inventory->item);
            $journal->save();
        }

        return $cost_of_sales;
    }

    public static function journalPos($cost_of_sales, $pos)
    {
        /**
         * COA CATEGORY     | ACCOUNT               | DEBIT         | CREDIT
         * ------------------------------------------------------------------
         * CURRENT ASSET    | PETTY CASH            | xxxx          |
         * LIABILITY        | INCOME TAX PAYABLE    |               | xxxx
         * INCOME           | SALE OF GOODS         |               | xxxx
         * EXPENSE          | SALES DISCOUNT        | xxxx          |
         * CURRENT ASSET    | INVENTORY             |               | xxxx
         * EXPENSE          | COST OF SALES         | xxxx          |
         * ------------------------------------------------------------------
         */
        if ($pos->tax_type == 'include') {
            $pos->subtotal = $pos->tax_base;
        }

        \Log::info('journal petty cash');
        // JOURNAL #1 of #6 - PETTY CASH
        $warehouse = Warehouse::find($pos->warehouse_id);
        if (! $warehouse->petty_cash_account) {
            throw new PointException('Please set petty cash account for your warehouse');
        }
        $journal = new Journal;
        $journal->form_date = $pos->formulir->form_date;
        $journal->coa_id = $warehouse->petty_cash_account;
        $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
        $journal->debit = $pos->total;
        $journal->form_journal_id = $pos->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        // JOURNAL #2 of #6 - INCOME TAX PAYABLE
        if ($pos->tax != 0) {
            \Log::info('journal income tax payable sales');

            $income_tax_payable_account = JournalHelper::getAccount('point sales pos', 'income tax payable');
            $journal = new Journal();
            $journal->form_date = $pos->formulir->form_date;
            $journal->coa_id = $income_tax_payable_account;
            $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
            $journal->credit = $pos->tax;
            $journal->form_journal_id = $pos->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        \Log::info('journal sales of goods');
        // JOURNAL #3 of #6 - SALE OF GOODS
        $sale_of_goods_account = JournalHelper::getAccount('point sales pos', 'sale of goods');
        $journal = new Journal;
        $journal->form_date = $pos->formulir->form_date;
        $journal->coa_id = $sale_of_goods_account;
        $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
        $journal->credit = $pos->subtotal;
        $journal->form_journal_id = $pos->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        if ($pos->discount) {
            // JOURNAL #4 of #6 - SALE DISCOUNT
            \Log::info('journal sales of goods');
            $sale_discount_account = JournalHelper::getAccount('point sales pos', 'sales discount');
            $journal = new Journal;
            $journal->form_date = $pos->formulir->form_date;
            $journal->coa_id = $sale_discount_account;
            $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
            $journal->debit = $pos->subtotal * $pos->discount / 100;
            $journal->form_journal_id = $pos->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        \Log::info('journal cost of sales');
        // JOURNAL #6 of #6 - COST OF SALES
        $cost_of_sales_account = JournalHelper::getAccount('point sales pos', 'cost of sales');
        $journal = new Journal;
        $journal->form_date = $pos->formulir->form_date;
        $journal->coa_id = $cost_of_sales_account;
        $journal->description = 'point of sales "' . $pos->formulir->form_number.'"';
        $journal->debit = $cost_of_sales;
        $journal->form_journal_id = $pos->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
    }
}