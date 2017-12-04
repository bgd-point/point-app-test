<?php

namespace Point\PointPurchasing\Vesa\Service;

use Point\PointPurchasing\Models\Service\Downpayment;

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
        $list_downpayment = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_downpayment->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/service/downpayment/vesa-approval'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve purchasing downpayment',
                'permission_slug' => 'approval.point.purchasing.service.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('purchasing/point/service/downpayment/' . $downpayment->id),
                'deadline' => $downpayment->formulir->form_date,
                'message' => 'please approve this purchasing downpayment ' . formulir_url($downpayment->formulir),
                'permission_slug' => 'approval.point.purchasing.service.downpayment'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_downpayment = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_downpayment->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/service/downpayment/vesa-rejected'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form downpayment',
                'permission_slug' => 'update.point.purchasing.service.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('purchasing/point/service/downpayment/' . $downpayment->id.'/edit'),
                'deadline' => $downpayment->formulir->form_date,
                'message' => formulir_url($downpayment->formulir) . ' Rejected, please edit your form purchasing downpayment',
                'permission_slug' => 'update.point.purchasing.service.downpayment'
            ]);
        }

        return $array;
    }
}
