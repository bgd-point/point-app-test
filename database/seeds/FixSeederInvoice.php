<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\PointExpedition\Models\Invoice;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\Invoice as InvoicePurchasing;
use Point\PointPurchasing\Models\Service\Invoice as ServiceInvoicePurchasing;

class FixSeederInvoice extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        \Log::info('---- Seeder Invoice Expedition Started ----');
        self::fixSeederInvoiceExpedition();
        \Log::info('---- Seeder Invoice Expedition Finished ----');
        \Log::info('---- Seeder Invoice Purchasing Started ----');
        self::fixSeederInvoicePurchasingInventory();
        \Log::info('---- Seeder Invoice Purchasing Finished ----');
        \Log::info('---- Seeder Service Invoice Purchasing Started ----');
		self::fixSeederInvoicePurchasingService();
        \Log::info('---- Seeder Service Invoice Purchasing Finished ----');
        \DB::commit();
    }

    public function fixSeederInvoiceExpedition()
    {
    	$list_invoice = Invoice::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->select('formulir.id')->get()->toArray();
        $journal = Journal::whereIn('form_journal_id', $list_invoice)->delete();
        $list_invoice = Invoice::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->selectOriginal()->get();
        
        foreach ($list_invoice as $invoice) {
        	// 1. JOURNAL ACCOUNT PAYABLE - EXP
        	\Log::info('jurnal account payable exp');
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
	        $journal->subledger_type = get_class(new Person());
	        $journal->save();

	        $expedition_cost = 0;
	        if ($invoice->type_of_tax == 'include') {
	            $expedition_cost = $invoice->tax_base;
	        } else {
	            $expedition_cost = $invoice->subtotal;
	        }

	        // 2. JOURNAL EXPEDITION EXPENSE
        	\Log::info('jurnal expedition cost');
	        
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
	        if ($invoice->tax != 0) {
        		\Log::info('jurnal income tax payable');

	            $income_tax_payable = JournalHelper::getAccount('point expedition', 'income tax receivable');
	            $position = JournalHelper::position($income_tax_payable);
	            $journal = new Journal();
	            $journal->form_date = $invoice->formulir->form_date;
	            $journal->coa_id = $income_tax_payable;
	            $journal->description = 'expedition invoice "' . $invoice->formulir->form_number . '"';
	            $journal->$position = $invoice->tax;
	            $journal->form_journal_id = $invoice->formulir_id;
	            $journal->form_reference_id;
	            $journal->subledger_id;
	            $journal->subledger_type;
	            $journal->save();
	        }

	        // 4. JOURNAL EXPEDITION DISCOUNT
	        if ($invoice->discount != 0) {
        		\Log::info('jurnal expedition discount');

	            $expedition_discount_account = JournalHelper::getAccount('point expedition', 'expedition discount');
	            $position = JournalHelper::position($expedition_discount_account);
	            $journal = new Journal;
	            $journal->form_date = $invoice->formulir->form_date;
	            $journal->coa_id = $expedition_discount_account;
	            $journal->description = 'expedition invoice "' . $invoice->formulir->form_number . '"';
	            $journal->$position = $invoice->discount * -1;
	            $journal->form_journal_id = $invoice->formulir_id;
	            $journal->form_reference_id;
	            $journal->subledger_id;
	            $journal->subledger_type;
	            $journal->save();
	        }

        	JournalHelper::checkJournalBalance($invoice->formulir_id);
        }
    }

    public function fixSeederInvoicePurchasingInventory()
    {
    	$list_invoice = InvoicePurchasing::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->select('formulir.id')->get()->toArray();
        $journal = Journal::whereIn('form_journal_id', $list_invoice)->delete();
        $list_invoice = InvoicePurchasing::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->selectOriginal()->get();
        \Log::info('Journal invoice purchase inventory started');
        foreach ($list_invoice as $invoice) {
        	$subtotal = 0;
        	foreach ($invoice->items as $invoice_detail) {
	            $warehouse_id = UserWarehouse::getWarehouse($invoice->formulir->created_by);
	            $goods_received_item = ReferHelper::getReferBy(get_class($invoice_detail), $invoice_detail->id, get_class($invoice), $invoice->id); 
	            if ($goods_received_item) {
	                $goods_received = GoodsReceived::find($goods_received_item->point_purchasing_goods_received_id); 
	                $warehouse_id = $goods_received->warehouse_id; 
	            }
	 
	            // Journal inventory
	            $total_per_row = $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
            	$subtotal += $total_per_row;
	            
	            if ($invoice->type_of_tax == 'include') {
	                $total_per_row = $total_per_row * 100 / 110;
	            }

	            \Log::info('Journal inventory invoice '. $invoice->formulir->id);
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

	            // insert new inventory
	            $item = Item::find($invoice_detail->item_id);
	            $inventory = new Inventory();
	            $inventory->formulir_id = $invoice->formulir->id;
	            $inventory->item_id = $item->id;
	            $inventory->quantity = $invoice_detail->quantity * $invoice_detail->converter;
	            $inventory->price = $invoice_detail->price / $invoice_detail->converter;
	            $inventory->form_date = $invoice->formulir->form_date;
	            $inventory->warehouse_id = $warehouse_id;

	            $inventory_helper = new InventoryHelper($inventory);
	            $inventory_helper->in();
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
	                'value_of_account_payable' => $invoice->total,
	                'value_of_income_tax_receiveable' => $invoice->tax,
	                'value_of_discount' => $invoice->discount * (-1),
	                'value_of_expedition_cost' => $invoice->expedition_fee,
	                'formulir' => $invoice->formulir,
	                'invoice' => $invoice
	            );
	            self::journalInvoicePurchasingInventory($data);
	        } elseif ($invoice->type_of_tax == 'include') {
	            $data = array(
	                'value_of_account_payable' => $invoice->total,
	                'value_of_income_tax_receiveable' => $invoice->tax,
	                'value_of_discount' => $invoice->discount,
	                'value_of_expedition_cost' => $invoice->expedition_fee,
	                'formulir' => $invoice->formulir,
	                'invoice' => $invoice
	            );
	            self::journalInvoicePurchasingInventory($data);
	        }

        	JournalHelper::checkJournalBalance($invoice->formulir_id);
        }
    }

    public function journalInvoicePurchasingInventory($data)
    {
        \Log::info('Journal account receiveable started');

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

        \Log::info('Journal income tax receiveable');

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

        \Log::info('Journal Purchase Discount');

        // 3. Journal Purchase Discount
        if ($data['invoice']->discount > 0) {
            $purchasing_discount = JournalHelper::getAccount('point purchasing', 'purchase discount');
            $position = JournalHelper::position($purchasing_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $purchasing_discount;
            $journal->description = 'invoice purchasing [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        \Log::info('Journal Expedition Cost');

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
        }
    }

    public function fixSeederInvoicePurchasingService()
    {
    	$list_invoice = ServiceInvoicePurchasing::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->select('formulir.id')->get()->toArray();
        $journal = Journal::whereIn('form_journal_id', $list_invoice)->delete();
        $inventory = Inventory::whereIn('formulir_id', $list_invoice)->delete();
        $list_invoice = ServiceInvoicePurchasing::joinFormulir()->whereIn('formulir.form_status', [0, 1])->notArchived()->approvalApproved()->selectOriginal()->get();
        \Log::info('Journal invoice service started');
        foreach ($list_invoice as $invoice) {
            $subtotal_service = 0;
            $subtotal_item = 0;

            foreach ($invoice->services as $invoice_detail) {
                $total_per_row = $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
                $subtotal_service += $total_per_row;
            }

            foreach ($invoice->items as $invoice_detail) {
                $total_per_row = $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
                if ($invoice->type_of_tax == 'include') {
	                $total_per_row = $total_per_row * 100 / 110;
	            }

                $subtotal_item += $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
                // Journal
	            $position = JournalHelper::position($invoice_detail->item->account_asset_id);
	            $journal = new Journal();
	            $journal->form_date = $invoice->formulir->form_date;
	            $journal->coa_id = $invoice_detail->item->account_asset_id;
	            $journal->description = 'Purchasing Service Invoice Item [' . $invoice->formulir->form_number.']';
	            $journal->$position = $total_per_row;
	            $journal->form_journal_id = $invoice->formulir_id;
	            $journal->form_reference_id;
	            $journal->subledger_id = $invoice_detail->item_id;
	            $journal->subledger_type = get_class($invoice_detail->item);
	            $journal->save();

	            // insert new inventory
	            $item = Item::find($invoice_detail->item_id);
	            $inventory = new Inventory();
	            $inventory->formulir_id = $invoice->formulir->id;
	            $inventory->item_id = $item->id;
	            $inventory->quantity = $invoice_detail->quantity * $invoice_detail->converter;
	            $inventory->price = $invoice_detail->price / $invoice_detail->converter;
	            $inventory->form_date = $invoice->formulir->form_date;
	            $inventory->warehouse_id = UserWarehouse::getWarehouse($invoice->formulir->created_by);

	            $inventory_helper = new InventoryHelper($inventory);
	            $inventory_helper->in();
            }

            $subtotal = $subtotal_item + $subtotal_service;
            $discount = $subtotal * $invoice->discount/100;
            $tax_base = $subtotal - $discount;
            $tax = 0;
            if ($invoice->type_of_tax == 'include') {
                $tax_base = $subtotal * 100 / 110;
                $tax = $subtotal * 10 / 100;
            	$subtotal_service = $subtotal_service * 100 / 110;
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

            $data = array(
	            'value_of_account_payable' => $invoice->total,
	            'value_of_income_tax_receiveable' => $tax,
	            'value_of_discount' => $discount * (-1),
	            'value_cost_of_service' => $subtotal_service,
	            'formulir' => $invoice->formulir,
	            'invoice' => $invoice
	        );
        	
        	self::journalService($data);

            JournalHelper::checkJournalBalance($invoice->formulir_id);
        }
    }

    public static function journalService($data)
    {
        // 1. Journal account payable
        $account_payable = JournalHelper::getAccount('point purchasing service', 'account payable');
        $position = JournalHelper::position($account_payable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_payable;
        $journal->description = 'Service Invoice Purchasing [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_payable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->supplier);
        $journal->save();

        // 2. Journal income tax receivable
        if ($data['invoice']->tax > 0) {
            $income_tax_receiveable = JournalHelper::getAccount('point purchasing service', 'income tax receivable');
            $position = JournalHelper::position($income_tax_receiveable);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $income_tax_receiveable;
            $journal->description = 'Service Invoice Purchasing [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_income_tax_receiveable'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }
        // 3. Journal Purchase Discount
        if ($data['invoice']->discount > 0) {
            $purchasing_discount = JournalHelper::getAccount('point purchasing service', 'purchase discount');
            $position = JournalHelper::position($purchasing_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $purchasing_discount;
            $journal->description = 'Service invoice purchasing [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }

        $cost_of_service = JournalHelper::getAccount('point purchasing service', 'service cost');
        $position = JournalHelper::position($cost_of_service);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $cost_of_service;
        $journal->description = 'Service invoice purchasing [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_cost_of_service'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
    }
}
