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

        // Grouping vesa
        if ($merge_into_group && $list_payment_reference->count() > 5) {
            array_push($array, [
                'url' => url('finance/point/payment/vesa-create'),
                'deadline' => $list_payment_reference->orderBy('id', 'DESC')->first()->reference->form_date,
                'message' => 'create a payment from pending list',
                'permission_slug' => 'menu.point.finance.cashier'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_payment_reference->get() as $payment_reference) {
            $model = $payment_reference->reference->formulirable_type;
            $url = $model::showUrl($payment_reference->reference->formulirable_id);
            array_push($array, [
                'url' => url('finance/point/payment/choose/' . $payment_reference->payment_reference_id),
                'deadline' => $payment_reference->required_date ? : $payment_reference->reference->form_date,
                'message' => 'create payment from formulir number <a href="'. $url . '">' . $payment_reference->reference->form_number . '</a>',
                'permission_slug' => 'menu.point.finance.cashier'
            ]);
        }

        return $array;
    }
}
