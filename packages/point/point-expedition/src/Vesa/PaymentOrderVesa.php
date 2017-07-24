<?php

namespace Point\PointExpedition\Vesa;

use Point\PointExpedition\Models\Invoice;
use Point\PointExpedition\Models\PaymentOrder;

trait PaymentOrderVesa
{
    public static function getVesa()
    {
        $array = self::vesaCreate();
        $array = self::vesaApproval($array);
        $array = self::vesaReject($array);

        return $array;
    }

    public static function getVesaApproval()
    {
        return self::vesaApproval([], false);
    }

    public static function getVesaCreate()
    {
        return self::vesaCreate([], false);
    }

    public static function getVesaReject()
    {
        return self::vesaReject([], false);
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_payment_order = self::joinFormulir()->open()->approvalPending()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_payment_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('expedition/point/payment-order/vesa-approval'),
                'deadline' => $list_payment_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve payment order',
                'permission_slug' => 'approval.point.expedition.payment.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_order->get() as $payment_order) {
            array_push($array, [
                'url' => url('expedition/point/payment-order/' . $payment_order->id),
                'deadline' => $payment_order->formulir->form_date,
                'message' => 'please approve this payment order ' . $payment_order->formulir->form_number,
                'permission_slug' => 'approval.point.expedition.payment.order'
            ]);
        }

        return $array;
    }

    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $list_invoice = Invoice::availableToPaymentOrder();

        // Grouping vesa
        if ($merge_into_group && $list_invoice->get()->count() > 5) {
            array_push($array, [
                'url' => url('expedition/point/payment-order/vesa-create-payment-order'),
                'deadline' => $list_invoice->orderBy('id', 'DESC')->first()->formulir->form_date,
                'message' => 'create payment order',
                'permission_slug' => 'create.point.expedition.payment.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_invoice->get() as $invoice) {
            array_push($array, [
                'url' => url('expedition/point/payment-order/create-step-2/' . $invoice->expedition_id),
                'deadline' => $invoice->formulir->form_date,
                'message' => 'create payment order from invoice ' . $invoice->formulir->form_number,
                'permission_slug' => 'create.point.expedition.payment.order'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_payment_order = self::joinFormulir()->open()->approvalRejected()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_payment_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('expedition/point/payment-order/vesa-rejected'),
                'deadline' => $list_payment_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.expedition.payment.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_order->get() as $payment_order) {
            array_push($array, [
                'url' => url('expedition/point/payment-order/' . $payment_order->id.'/edit'),
                'deadline' => $payment_order->formulir->form_date,
                'message' => $payment_order->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.expedition.payment.order'
            ]);
        }

        return $array;
    }
}
