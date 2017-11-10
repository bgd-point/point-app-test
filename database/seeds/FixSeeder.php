<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\AllocationHelper;

class FixSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        \Point\Framework\Models\Master\AllocationReport::where('id','>',0)->delete();

        $sales_invoices = \Point\PointSales\Models\Sales\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
        foreach ($sales_invoices as $sales_invoice) {
            foreach ($sales_invoice->items as $item) {
                $total = $item->price * $item->quantity;
                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount * -1, $item->item_notes);
            }
        }

        $sales_invoices = \Point\PointSales\Models\Service\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
        foreach ($sales_invoices as $sales_invoice) {
            foreach ($sales_invoice->items as $item) {
                $total = $item->price * $item->quantity;
                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount * -1, $item->item_notes);
            }

            foreach ($sales_invoice->services as $item) {
                $total = $item->price * $item->quantity;
                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount * -1, $item->service_notes);
            }
        }

        $sales_invoices = Point\PointPurchasing\Models\Inventory\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
        foreach ($sales_invoices as $sales_invoice) {
            foreach ($sales_invoice->items as $item) {
                $total = $item->price * $item->quantity;
                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount, $item->item_notes);
            }
        }

        $sales_invoices = Point\PointPurchasing\Models\Service\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
        foreach ($sales_invoices as $sales_invoice) {
            foreach ($sales_invoice->items as $item) {
                $total = $item->price * $item->quantity;
                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount, $item->item_notes);
            }

            foreach ($sales_invoice->services as $item) {
                $total = $item->price * $item->quantity;
                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount, $item->service_notes);
            }
        }

        \DB::commit();
    }
}
