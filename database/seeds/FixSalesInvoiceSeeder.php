<?php

use Illuminate\Database\Seeder;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\InvoiceItem;

class FixSalesInvoiceSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $invoices = Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();

        foreach($invoices as $invoice) {
            $archived = Invoice::joinFormulir()->selectOriginal()->where('archived', $invoice->formulir->form_number)->orderBy('id', 'asc')->first();
            if ($archived) {
                foreach($archived->items as $item) {
                    $invoice_item = new InvoiceItem;
                    $invoice_item->point_sales_invoice_id = $invoice->id;
                    $invoice_item->item_id = $item->item_id;
                    $invoice_item->quantity = $item->quantity;
                    $invoice_item->price = $item->price;
                    $invoice_item->discount = $item->discount;
                    $invoice_item->unit = $item->unit;
                    $invoice_item->allocation_id = $item->allocation_id;
                    $invoice_item->converter = 1;
                    $invoice_item->save();
                }
            }
        }

        \DB::commit();
    }
}
