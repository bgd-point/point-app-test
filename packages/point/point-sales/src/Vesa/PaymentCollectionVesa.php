<?php

namespace Point\PointSales\Vesa;

use Point\PointSales\Models\PaymentOrder;
use Point\PointSales\Models\Sales\Invoice;

trait PaymentCollectionVesa
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
            ->selectOriginal()
            ->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_invoice->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/payment-collection/vesa-create'),
                'deadline' => $list_invoice->orderBy('due_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_invoice->orderBy('due_date')->first()->due_date) ? true : false,
                'message' => 'Make a payment collection from invoice',
                'permission_slug' => 'create.point.sales.payment.collection'
            ]);
            return $array;
        }

        // Push all
        foreach ($list_invoice->get() as $invoice) {
            array_push($array, [
                'url' => url('sales/point/indirect/payment-collection/create-step-2/' . $invoice->person_id),
                'deadline' => $invoice->due_date ? : $invoice->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $invoice->due_date) ? true : false,
                'message' => 'Make a payment collection from invoice number ' . $invoice->formulir->form_number,
                'permission_slug' => 'create.point.sales.payment.collection'
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
                'url' => url('sales/point/indirect/payment-collection/vesa-approval'),
                'deadline' => $list_payment_collection->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve payment collection sales',
                'permission_slug' => 'approval.point.sales.payment.collection'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_collection->get() as $payment_collection) {
            array_push($array, [
                'url' => url('sales/point/indirect/payment-collection/' . $payment_collection->id),
                'deadline' => $payment_collection->due_date ? : $payment_collection->formulir->form_date,
                'message' => 'Please approve this payment collection from sales number ' . $payment_collection->formulir->form_number,
                'permission_slug' => 'approval.point.sales.payment.collection'
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
                'url' => url('sales/point/indirect/payment-collection/vesa-rejected'),
                'deadline' => $list_payment_collection->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form payment collection sales',
                'permission_slug' => 'update.point.sales.payment.collection'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_collection->get() as $payment_collection) {
            array_push($array, [
                'url' => url('sales/point/indirect/payment-collection/' . $payment_collection->id.'/edit'),
                'deadline' => $payment_collection->due_date ? : $payment_collection->formulir->form_date,
                'message' => $payment_collection->formulir->form_number. ' Rejected, please edit your form payment collection sales',
                'permission_slug' => 'update.point.sales.payment.collection'
            ]);
        }

        return $array;
    }
}
