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
                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount, $item->item_notes);
            }
        }

        $sales_invoices = \Point\PointSales\Models\Service\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
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

        $sales_invoices = Point\PointPurchasing\Models\Inventory\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
        foreach ($sales_invoices as $sales_invoice) {
            foreach ($sales_invoice->items as $item) {
                $total = $item->price * $item->quantity;
                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount * -1, $item->item_notes);
            }
        }

        $sales_invoices = Point\PointPurchasing\Models\Service\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
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

//        $payment_orders = \Point\PointFinance\Models\PaymentOrder\PaymentOrder::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
//        foreach ($payment_orders as $order) {
//            foreach ($order->detail() as $details) {
//                AllocationHelper::save($order->formulir->id, $details->allocation_id, $detail->amount * -1, $details->notes_detail);
//            }
//        }

        $cash_details = \Point\PointFinance\Models\Cash\CashDetail::joinCash()
            ->notArchived()->notCanceled()->where('form_reference_id', NULL)->selectOriginal()->get();

        foreach ($cash_details as $detail) {
            AllocationHelper::save($detail->cash->formulir->id, $detail->allocation_id, $detail->amount, $detail->notes_detail);
        }

        $bank_details = \Point\PointFinance\Models\Bank\BankDetail::joinBank()
            ->notArchived()->notCanceled()->where('form_reference_id', NULL)->selectOriginal()->get();

        foreach ($bank_details as $detail) {
            AllocationHelper::save($detail->bank->formulir->id, $detail->allocation_id, $detail->amount, $detail->notes_detail);
        }

        \DB::commit();
    }
}
