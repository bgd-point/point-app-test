<?php

namespace Point\PointSales\Vesa;

use Point\PointSales\Models\Service\Downpayment;

trait ServiceDownpaymentVesa
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
        $list_downpayment = self::joinFormulir()->with('person')->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_downpayment->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/service/downpayment/vesa-approval'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'you have many sales service downpayments waiting to be approved',
                'permission_slug' => 'approval.point.sales.service.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('sales/point/service/downpayment/' . $downpayment->id),
                'deadline' => $downpayment->formulir->form_date,
                'message' => 'please approve sales service downpayment '
                    . formulir_url($downpayment->formulir)
                    . ' customer <strong>' . $downpayment->person->name . '</strong> amount '
                    . number_format_price($downpayment->amount),
                'permission_slug' => 'approval.point.sales.service.downpayment'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_downpayment = self::joinFormulir()->with('person')->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_downpayment->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/service/downpayment/vesa-rejected'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'you have many rejected sales service downpayments',
                'permission_slug' => 'update.point.sales.service.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('sales/point/service/downpayment/' . $downpayment->id.'/edit'),
                'deadline' => $downpayment->formulir->form_date,
                'message' => 'sales service downpayment '
                    . formulir_url($downpayment->formulir)
                    . ' customer <strong>' . $downpayment->person->name . '</strong> amount'
                    . number_format_price($downpayment->amount)
                    . ' Rejected, please edit your form',
                'permission_slug' => 'update.point.sales.service.downpayment'
            ]);
        }

        return $array;
    }
}
