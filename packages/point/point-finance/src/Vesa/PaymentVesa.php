<?php

namespace Point\PointFinance\Vesa;

use Point\PointFinance\Models\PaymentReference;

trait PaymentVesa
{
    public static function getVesa()
    {
        $array = self::vesaCreate();
        return $array;
    }

    public static function getVesaCreate()
    {
        return self::vesaCreate([], false);
    }

    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $list_payment_reference = PaymentReference::whereNull('point_finance_payment_id');

        // Push all
        foreach ($list_payment_reference->get() as $payment_reference) {
            array_push($array, [
                'url' => url('finance/point/payment/choose/' . $payment_reference->payment_reference_id),
                'deadline' => $payment_reference->required_date ? : $payment_reference->reference->form_date,
                'message' => 'create payment from formulir number ' . formulir_url($payment_reference->reference),
                'permission_slug' => 'menu.point.finance.cashier',
                'data' => $payment_reference
            ]);
        }

        return $array;
    }
}
