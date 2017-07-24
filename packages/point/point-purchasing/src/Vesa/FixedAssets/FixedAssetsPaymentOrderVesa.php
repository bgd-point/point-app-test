<?php

namespace Point\PointPurchasing\Vesa\FixedAssets;

use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPaymentOrder;
use Point\PointPurchasing\Models\FixedAssets\Invoice;

trait FixedAssetsPaymentOrderVesa
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
        $list_invoice = FixedAssetsInvoice::joinFormulir()
            ->availableToPaymentOrder()
            ->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_invoice->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/payment-order/vesa-create'),
                'deadline' => $list_invoice->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Make a payment order from invoice',
                'permission_slug' => 'create.point.purchasing.payment.order.fixed.assets'
            ]);
            return $array;
        }
        // Push all
        foreach ($list_invoice->get() as $invoice) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/payment-order/create-step-2/' . $invoice->supplier_id),
                'deadline' => $invoice->required_date ? : $invoice->formulir->form_date,
                'message' => 'Make a payment order from invoice number ' . $invoice->formulir->form_number,
                'permission_slug' => 'create.point.purchasing.payment.order.fixed.assets'
            ]);
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_payment_order = self::joinFormulir()->open()->approvalPending()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_payment_order->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/payment-order/vesa-approval'),
                'deadline' => $list_payment_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve payment order purchasing',
                'permission_slug' => 'approval.point.purchasing.payment.order.fixed.assets'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_order->get() as $payment_order) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/payment-order/' . $payment_order->id),
                'deadline' => $payment_order->required_date ? : $payment_order->formulir->form_date,
                'message' => 'Please approve this payment order purchasing number ' . $payment_order->formulir->form_number,
                'permission_slug' => 'approval.point.purchasing.payment.order.fixed.assets'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_payment_order = self::joinFormulir()->open()->approvalRejected()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_payment_order->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/payment-order/vesa-rejected'),
                'deadline' => $list_payment_order->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form payment order purchasing',
                'permission_slug' => 'update.point.purchasing.payment.order.fixed.assets'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_order->get() as $payment_order) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/payment-order/' . $payment_order->id.'/edit'),
                'deadline' => $payment_order->required_date ? : $payment_order->formulir->form_date,
                'message' => $payment_order->formulir->form_number. ' Rejected, please edit your form payment order purchasing',
                'permission_slug' => 'update.point.purchasing.payment.order.fixed.assets'
            ]);
        }

        return $array;
    }
}
