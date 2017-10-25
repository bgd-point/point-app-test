<?php

use Illuminate\Database\Seeder;
use Point\PointFinance\Models\PaymentOrder\PaymentOrder;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;

class InjectFinanceReferenceManukanSeeder extends Seeder
{
    public function run()
    {
        $list_payment_order = PaymentOrder::joinFormulir()
            ->approvalApproved()
            ->notArchived()
            ->open()
            ->selectOriginal()
            ->get();
        foreach ($list_payment_order as $payment_order) {
            $payment_reference = PaymentReference::where('payment_reference_id', $payment_order->formulir_id)->first();
            if (! $payment_reference) {
                self::insertPaymentReference($payment_order);
            }
        }
    }

    public function insertPaymentReference($payment_order)
    {
        DB::beginTransaction();

        $payment_reference = new PaymentReference;
        $payment_reference->payment_reference_id = $payment_order->formulir_id;
        $payment_reference->person_id = $payment_order->person_id;
        $payment_reference->payment_flow = 'out';
        $payment_reference->payment_type = $payment_order->payment_type;
        $payment_reference->save();

        $total = 0;

        foreach ($payment_order->detail as $payment_order_detail) {
            $total += $payment_order_detail->amount;
            $payment_reference_detail = new PaymentReferenceDetail;
            $payment_reference_detail->point_finance_payment_reference_id = $payment_reference->id;
            $payment_reference_detail->coa_id = $payment_order_detail->coa_id;
            $payment_reference_detail->allocation_id = $payment_order_detail->allocation_id;
            $payment_reference_detail->notes_detail = $payment_order_detail->notes_detail;
            $payment_reference_detail->amount = $payment_order_detail->amount;
            $payment_reference_detail->save();
        }

        $payment_reference->total = $total;
        $payment_reference->save();

        DB::commit();
    }
}
