<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\ItemUnit;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Invoice as SalesInvoice;
use Point\PointPurchasing\Models\Inventory\Invoice as PurchasingInvoice;

class RecalculateTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'recalculate transaction';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('recalculating transaction purchasing');

        $list_purchasing = PurchasingInvoice::join('formulir', 'formulir.id', '=', 'point_purchasing_invoice.formulir_id')
            ->where('point_purchasing_invoice.type_of_tax', 'include')
            ->select('point_purchasing_invoice.*')
            ->get();

        foreach ($list_purchasing as $invoice) {
            $this->comment($invoice->formulir_id);
            $dc = new \stdClass();
            $dc->debit = 0;
            $dc->credit = 0;

            $invoice->tax = $invoice->subtotal * 11 / 111;
            $invoice->tax_base = $invoice->subtotal - $invoice->tax;
            $invoice->save();

            Inventory::where('formulir_id', '=', $invoice->formulir->id)->delete();
            Journal::where('form_journal_id', '=', $invoice->formulir->id)->delete();

            foreach ($invoice->items as $invoice_detail) {
                $goods_received_item = ReferHelper::getReferBy(get_class($invoice_detail), $invoice_detail->id, get_class($invoice), $invoice->id);
                if ($goods_received_item) {
                    $goods_received = GoodsReceived::find($goods_received_item->point_purchasing_goods_received_id);
                    $warehouse_id = $goods_received->warehouse_id;
                }
    
                // Journal inventory
                $total_per_row = $invoice_detail->quantity * $invoice_detail->price - $invoice_detail->quantity * $invoice_detail->price / 100 * $invoice_detail->discount;
                if ($invoice->discount) {
                    $discounty = $total_per_row * $invoice->discount / $invoice->subtotal;
                    $total_per_row = $total_per_row - $discounty;
                }
                if ($invoice->type_of_tax == 'include') {
                    $total_per_row = $total_per_row * 100 / 111;
                }

                $position = JournalHelper::position($invoice_detail->item->account_asset_id);

                $journal = new Journal();
                $journal->form_date = $invoice->formulir->approval_at;
                $journal->coa_id = $invoice_detail->item->account_asset_id;
                $journal->description = 'Goods Received [' . $invoice->formulir->form_number.']';
                $journal->$position = $total_per_row;
                $journal->form_journal_id = $invoice->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $invoice_detail->item_id;
                $journal->subledger_type = get_class($invoice_detail->item);
                $journal->save();

                $dc->debit += round($total_per_row, 4);

                // insert new inventory
                $item = Item::find($invoice_detail->item_id);
                $inventory = new Inventory();
                $inventory->formulir_id = $invoice->formulir->id;
                $inventory->item_id = $item->id;
                $inventory->quantity = $invoice_detail->quantity * $invoice_detail->converter;

                $price = $invoice_detail->price;
                if ($invoice->type_of_tax == 'include') {
                    $inventory->price = $price / 1.11 / $invoice_detail->converter;
                } else {
                    $inventory->price = $price / $invoice_detail->converter;
                }
                $inventory->form_date = $invoice->formulir->approval_at;
                $inventory->warehouse_id = $warehouse_id;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();
            }

            // Journal tax exclude and non-tax
            if ($invoice->type_of_tax == 'exclude' || $invoice->type_of_tax == 'non') {
                $data = array(
                    'value_of_account_payable' => $invoice->total,
                    'value_of_income_tax_receiveable' => $invoice->tax,
                    'value_of_expedition_cost' => $invoice->expedition_fee,
                    'formulir' => $invoice->formulir,
                    'invoice' => $invoice
                );
                $dc2 = self::journalPurchasing($data);
                $dc->debit += round($dc2->debit , 4);
                $dc->credit += round($dc2->credit , 4);
            } elseif ($invoice->type_of_tax == 'include') {
                $data = array(
                    'value_of_account_payable' => $invoice->total,
                    'value_of_income_tax_receiveable' => $invoice->tax,
                    'value_of_expedition_cost' => $invoice->expedition_fee,
                    'formulir' => $invoice->formulir,
                    'invoice' => $invoice
                );
                $dc2 = self::journalPurchasing($data);
                $dc->debit += round($dc2->debit, 4);
                $dc->credit += round($dc2->credit, 4);
            }

            
            if($dc->debit !== $dc->credit) {
                
                $factor = pow(10, 4);
                
                $fdebit = floor($dc->debit * $factor) / $factor;
                $fcredit = floor($dc->credit * $factor) / $factor;

                $journal = new Journal();
                $journal->form_date = $invoice->formulir->approval_at;
                $coa_selisih = \DB::table('coa')->where('name', 'PENDAPATAN (BEBAN) SELISIH PEMBAYARAN')->first();
                if ($coa_selisih) {
                    $journal->coa_id = $coa_selisih->id;
                } else {
                    $journal->coa_id = 1193;
                }
                $journal->description = 'Selisih pembulatan';
                $journal->debit = JournalHelper::getDiff($invoice->formulir_id);
                $journal->credit = 0;
                $journal->form_journal_id = $invoice->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }
        }

        $this->comment('recalculating transaction sales');
        
        $list_sales = SalesInvoice::join('formulir', 'formulir.id', '=', 'point_sales_invoice.formulir_id')
            ->where('point_sales_invoice.type_of_tax', 'include')
            ->select('point_sales_invoice.*')
            ->get();

        foreach ($list_sales as $invoice) {
            $this->comment($invoice->formulir_id);
            Inventory::where('formulir_id', '=', $invoice->formulir->id)->delete();
            Journal::where('form_journal_id', '=', $invoice->formulir->id)->delete();

            $cost_of_sales = 0;
            foreach ($invoice->items as $invoice_detail) {
                $delivery_order_item = ReferHelper::getReferBy(get_class($invoice_detail), $invoice_detail->id, get_class($invoice), $invoice->id);
    
                if ($delivery_order_item) {
                    $delivery_order = DeliveryOrder::find($delivery_order_item->point_sales_delivery_order_id);
                    $warehouse_id = $delivery_order->warehouse_id;
                }
                $item = Item::find($invoice_detail->item_id);
                $inventory = new Inventory();
                $inventory->formulir_id = $invoice->formulir->id;
                $inventory->item_id = $item->id;
                $inventory->quantity = $invoice_detail->quantity * $invoice_detail->converter;
                $inventory->price = InventoryHelper::getCostOfSales($invoice->formulir->approval_at, $item->id, $warehouse_id);
                $inventory->form_date = $invoice->formulir->approval_at;
                $inventory->warehouse_id = $warehouse_id;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->out();

                $cost = InventoryHelper::getCostOfSales(\Carbon::now(), $inventory->item_id, $inventory->warehouse_id) * abs($inventory->quantity);
                $cost_of_sales += $cost;

                $journal = new Journal;
                $journal->form_date = $invoice->formulir->approval_at;
                $journal->coa_id = $inventory->item->account_asset_id;
                $journal->description = 'invoice "' . $inventory->item->codeName.'"';
                $journal->credit = $cost;
                $journal->form_journal_id = $invoice->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $inventory->item_id;
                $journal->subledger_type = get_class($inventory->item);
                $journal->save();
            }
            
            // Journal tax exclude and non-tax
            if ($invoice->type_of_tax == 'exclude' || $invoice->type_of_tax == 'non') {
                $data = array(
                    'value_of_account_receivable' => $invoice->total,
                    'value_of_income_tax_payable' => $invoice->tax,
                    'value_of_sale_of_goods' => $invoice->subtotal,
                    'value_of_cost_of_sales' => $cost_of_sales,
                    'value_of_discount' => $invoice->discount * (-1),
                    'value_of_expedition_income' => $invoice->expedition_fee,
                    'formulir' => $invoice->formulir,
                    'invoice' => $invoice
                );
                self::journalSales($data);
            }

            // Journal tax include
            if ($invoice->type_of_tax == 'include') {
                $data = array(
                    'value_of_account_receivable' => $invoice->total,
                    'value_of_income_tax_payable' => $invoice->tax,
                    'value_of_sale_of_goods' => $invoice->tax_base,
                    'value_of_cost_of_sales' => $cost_of_sales,
                    'value_of_discount' => $invoice->discount,
                    'value_of_expedition_income' => $invoice->expedition_fee,
                    'formulir' => $invoice->formulir,
                    'invoice' => $invoice
                );
                self::journalSales($data);
            }
        }

        \DB::commit();
    }

    public static function journalPurchasing($data)
    {
        $dc = new \stdClass();
        $dc->debit = 0;
        $dc->credit = 0;

        // 1. Journal account receiveable
        $account_receiveable = JournalHelper::getAccount('point purchasing', 'account payable');
        $position = JournalHelper::position($account_receiveable);
        
        $journal = new Journal;
        $journal->form_date = $data['formulir']->approval_at;
        $journal->coa_id = $account_receiveable;
        $journal->description = 'Invoice Purchasing [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_payable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->supplier_id;
        $journal->subledger_type = get_class($data['invoice']->supplier);
        $journal->save();
        
        $dc->credit += round($data['value_of_account_payable'], 4);

        // 2. Journal income tax receiveable
        $income_tax_receiveable = JournalHelper::getAccount('point purchasing', 'income tax receivable');
        $position = JournalHelper::position($income_tax_receiveable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->approval_at;
        $journal->coa_id = $income_tax_receiveable;
        $journal->description = 'Invoice Purchasing [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_income_tax_receiveable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
        
        $dc->debit += round($data['value_of_income_tax_receiveable'], 4);

        // 3. Journal Expedition Cost
        if ($data['invoice']->expedition_fee > 0) {
            $expedition = JournalHelper::getAccount('point purchasing', 'expedition cost');
            $position = JournalHelper::position($expedition);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->approval_at;
            $journal->coa_id = $expedition;
            $journal->description = 'Invoice Purchasing [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_expedition_cost'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            $dc->debit += round($data['value_of_expedition_cost'], 4);
        }
        return $dc;
    }

    public static function journalSales($data)
    {
        // 1. Journal Account Receivable
        $account_receivable = JournalHelper::getAccount('point sales indirect', 'account receivable');
        $position = JournalHelper::position($account_receivable);
        
        $journal = new Journal;
        $journal->form_date = $data['invoice']->formulir->approval_at;
        $journal->coa_id = $account_receivable;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_receivable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        // 2. Journal Income Tax Payable
        if ($data['invoice']->tax != 0) {
            $income_tax_receivable = JournalHelper::getAccount('point sales indirect', 'income tax payable');
            $position = JournalHelper::position($income_tax_receivable);
            $journal = new Journal;
            $journal->form_date = $data['invoice']->formulir->approval_at;
            $journal->coa_id = $income_tax_receivable;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_income_tax_payable'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }
        
        // 3. Journal Sales Of Goods
        $sales_of_goods = JournalHelper::getAccount('point sales indirect', 'sale of goods');
        $position = JournalHelper::position($sales_of_goods);
        $journal = new Journal;
        $journal->form_date = $data['invoice']->formulir->approval_at;
        $journal->coa_id = $sales_of_goods;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_sale_of_goods'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        // 4. Journal Sales Discount
        if ($data['invoice']->discount > 0) {
            $sales_discount = JournalHelper::getAccount('point sales indirect', 'sales discount');
            $position = JournalHelper::position($sales_discount);
            $journal = new Journal;
            $journal->form_date = $data['invoice']->formulir->approval_at;
            $journal->coa_id = $sales_discount;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        // 5. Journal Expedition Cost
        if ($data['invoice']->expedition_fee > 0) {
            $cost_of_sales = JournalHelper::getAccount('point sales indirect', 'expedition income');
            $position = JournalHelper::position($cost_of_sales);
            $journal = new Journal;
            $journal->form_date = $data['invoice']->formulir->approval_at;
            $journal->coa_id = $cost_of_sales;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_expedition_income'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        $cost_of_sales_account = JournalHelper::getAccount('point sales indirect', 'cost of sales');
        $journal = new Journal;
        $journal->form_date = $data['invoice']->formulir->approval_at;
        $journal->coa_id = $cost_of_sales_account;
        $journal->description = 'invoice indirect sales "' . $data['formulir']->form_number.'"';
        $journal->debit = $data['value_of_cost_of_sales'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
    }
}