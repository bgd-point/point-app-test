<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Refer;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\InvoiceItem;

class FixSalesInvoiceSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        Refer::where('by_type', 'Point\PointSales\Models\Sales\DeliveryOrderItem')->delete();

        \DB::commit();

        $invoices = Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
        foreach ($invoices as $invoice) {
            \Log::info($invoice->formulir->form_number);
            $reference = FormulirLock::where('locking_id', $invoice->formulir_id)->where('locked', 1)->first();
            \Log::info($reference);

            if ($reference) {
                $delivery_order = DeliveryOrder::where('formulir_id', $reference->locked_id)->first();
                foreach($invoice->items as $invoice_item) {
                    $close = true;
                    foreach ($delivery_order->items as $delivery_order_item) {
                        if ($delivery_order_item->item_id == $invoice_item->item_id) {
                            ReferHelper::create(
                                'Point\PointSales\Models\Sales\DeliveryOrderItem',
                                $delivery_order_item->id,
                                get_class($invoice_item),
                                $invoice_item->id,
                                get_class($invoice),
                                $invoice->id,
                                $invoice_item->quantity
                            );
                        }

                        $refers = Refer::where('by_type', '=', 'Point\PointSales\Models\Sales\DeliveryOrderItem')
                            ->where('by_id', '=', $delivery_order_item->id)
                            ->where('status', '=', true)
                            ->get();

                        $value = 0;
                        foreach ($refers as $refer) {
                            $value += $refer->value;
                        }

                        \Log::info(number_format_quantity($value) . ' != ' .number_format_quantity($delivery_order_item->quantity));
                        if (number_format_quantity($value) != number_format_quantity($delivery_order_item->quantity)) {
                            $close = false;
                        }
                    }

                    if ($close) {
                        $delivery_order->formulir->form_status = 1;
                        $delivery_order->formulir->save();
                    }

                }
            }
        }

        \DB::commit();
    }
}
