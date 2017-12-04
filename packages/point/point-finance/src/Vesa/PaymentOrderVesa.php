<?php

namespace Point\PointFinance\Vesa;

use Point\PointFinance\Models\PaymentOrder\PaymentOrder;

trait PaymentOrderVesa
{
    public static function getVesa()
    {
        $array = self::vesaApproval();
        $array = self::vesaReject($array);
        return $array;
    }

    public static function getVesaApproval()
    {
        return self::vesaApproval([], false);
    }

    public static function getVesaReject()
    {
        return self::vesaReject([], false);
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_payment_order = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_payment_order->count() > 5) {
            array_push($array, [
                'url' => url('finance/point/payment-order/vesa-approval'),
                'deadline' => $list_payment_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve payment order',
                'permission_slug' => 'approval.point.finance.payment.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_order->get() as $payment_order) {
            array_push($array, [
                'url' => url('finance/point/payment-order/' . $payment_order->id),
                'deadline' => $payment_order->formulir->form_date,
                'message' => 'please approve this payment order ' . formulir_url($payment_order->formulir),
                'permission_slug' => 'approval.point.finance.payment.order'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_payment_order = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_payment_order->count() > 5) {
            array_push($array, [
                'url' => url('finance/point/payment-order/vesa-rejected'),
                'deadline' => $list_payment_order->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.finance.payment.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_order->get() as $payment_order) {
            array_push($array, [
                'url' => url('finance/point/payment-order/' . $payment_order->id.'/edit'),
                'deadline' => $payment_order->required_date ? : $payment_order->formulir->form_date,
                'message' => formulir_url($payment_order->formulir) . ' Rejected, please edit your form',
                'permission_slug' => 'update.point.finance.payment.order'
            ]);
        }

        return $array;
    }
}
