<?php

namespace Point\PointPurchasing\Vesa\Service;

use Point\PointPurchasing\Models\Service\Invoice;

trait ServicePaymentOrderVesa
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
            ->availableToPaymentOrder()
            ->groupBy('person_id')
            ->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_invoice->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/service/payment-order/vesa-create'),
                'deadline' => $list_invoice->orderBy('form_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_invoice->orderBy('due_date')->first()->due_date) ? true : false,
                'message' => 'Make a payment order from invoice',
                'permission_slug' => 'create.point.purchasing.service.payment.order'
            ]);
            return $array;
        }
        // Push all
        foreach ($list_invoice->get() as $invoice) {
            array_push($array, [
                'url' => url('purchasing/point/service/payment-order/create-step-2/' . $invoice->person_id),
                'deadline' => $invoice->due_date ? : $invoice->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $invoice->due_date) ? true : false,
                'message' => 'Make a payment order from invoice number ' . formulir_url($invoice->formulir),
                'permission_slug' => 'create.point.purchasing.service.payment.order'
            ]);
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_payment_order = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_payment_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/service/payment-order/vesa-approval'),
                'deadline' => $list_payment_order->orderBy('form_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_payment_order->orderBy('due_date')->first()->due_date) ? true : false,
                'message' => 'please approve payment order service',
                'permission_slug' => 'approval.point.purchasing.service.payment.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_order->get() as $payment_order) {
            array_push($array, [
                'url' => url('purchasing/point/service/payment-order/' . $payment_order->id),
                'deadline' => $payment_order->due_date ? : $payment_order->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $payment_order->due_date) ? true : false,
                'message' => 'Please approve this payment order service number ' . formulir_url($payment_order->formulir),
                'permission_slug' => 'approval.point.purchasing.service.payment.order'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_payment_order = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_payment_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/service/payment-order/vesa-rejected'),
                'deadline' => $list_payment_order->orderBy('due_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_payment_order->orderBy('due_date')->first()->due_date) ? true : false,
                'message' => 'Rejected, please edit your form payment order service',
                'permission_slug' => 'update.point.purchasing.service.payment.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_order->get() as $payment_order) {
            array_push($array, [
                'url' => url('purchasing/point/service/payment-order/' . $payment_order->id.'/edit'),
                'deadline' => $payment_order->due_date ? : $payment_order->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $payment_order->due_date) ? true : false,
                'message' => formulir_url($payment_order->formulir) . ' Rejected, please edit your form payment order service',
                'permission_slug' => 'update.point.purchasing.service.payment.order'
            ]);
        }

        return $array;
    }
}
