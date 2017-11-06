<?php

namespace Point\PointSales\Vesa;

use Point\PointSales\Models\PaymentOrder;
use Point\PointSales\Models\Service\Invoice;

trait ServicePaymentCollectionVesa
{
    public static function getVesa()
    {
        $array = self::vesaCreate();
        $array = self::vesaApproval($array);
        $array = self::vesaReject($array);

        return $array;
    }

    public static function getVesaCreate()
    {
        return self::vesaCreate([], false);
    }

    public static function getVesaApproval()
    {
        return self::vesaApproval([], false);
    }

    public static function getVesaReject()
    {
        return self::vesaReject([], false);
    }

    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $list_invoice = Invoice::joinFormulir()
            ->availableToPaymentCollection()
            ->groupBy('person_id')
            ->notArchived()
            ->selectOriginal();
            
        // Grouping vesa
        if ($merge_into_group && $list_invoice->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/service/payment-collection/vesa-create'),
                'deadline' => $list_invoice->orderBy('due_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_invoice->orderBy('due_date')->first()->due_date) ? true : false,
                'message' => 'Make a payment collection from invoice',
                'permission_slug' => 'create.point.sales.service.payment.collection'
            ]);
            return $array;
        }
        // Push all
        foreach ($list_invoice->get() as $invoice) {
            array_push($array, [
                'url' => url('sales/point/service/payment-collection/create-step-2/' . $invoice->person_id),
                'deadline' => $invoice->due_date ? : $invoice->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $invoice->due_date) ? true : false,
                'message' => 'Make a payment collection from invoice number ' . formulir_url($invoice->formulir),
                'permission_slug' => 'create.point.sales.service.payment.collection'
            ]);
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_payment_collection = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_payment_collection->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/service/payment-collection/vesa-approval'),
                'deadline' => $list_payment_collection->orderBy('due_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_payment_collection->orderBy('due_date')->first()->due_date) ? true : false,
                'message' => 'please approve payment collection service',
                'permission_slug' => 'approval.point.sales.service.payment.collection'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_collection->get() as $payment_collection) {
            array_push($array, [
                'url' => url('sales/point/service/payment-collection/' . $payment_collection->id),
                'deadline' => $payment_collection->due_date ? : $payment_collection->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $payment_collection->due_date) ? true : false,
                'message' => 'Please approve this payment collection service number ' . formulir_url($payment_collection->formulir),
                'permission_slug' => 'approval.point.sales.service.payment.collection'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_payment_collection = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_payment_collection->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/service/payment-collection/vesa-rejected'),
                'deadline' => $list_payment_collection->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form payment collection service',
                'permission_slug' => 'update.point.sales.service.payment.collection'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_collection->get() as $payment_collection) {
            array_push($array, [
                'url' => url('sales/point/service/payment-collection/' . $payment_collection->id.'/edit'),
                'deadline' => $payment_collection->due_date ? : $payment_collection->formulir->form_date,
                'message' => formulir_url($payment_collection->formulir) . ' Rejected, please edit your form payment collection service',
                'permission_slug' => 'update.point.sales.service.payment.collection'
            ]);
        }

        return $array;
    }
}
