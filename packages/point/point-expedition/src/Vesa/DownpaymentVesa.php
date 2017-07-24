<?php

namespace Point\PointExpedition\Vesa;

use Point\PointExpedition\Models\Downpayment;

trait DownpaymentVesa
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
        $list_downpayment = self::joinFormulir()->open()->approvalPending()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_downpayment->get()->count() > 5) {
            array_push($array, [
                'url' => url('expedition/point/downpayment/vesa-approval'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve downpayment',
                'permission_slug' => 'approval.point.expedition.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('expedition/point/downpayment/' . $downpayment->id),
                'deadline' => $downpayment->formulir->form_date,
                'message' => 'Please approve this downpayment ' . $downpayment->formulir->form_number,
                'permission_slug' => 'approval.point.expedition.downpayment'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_downpayment = self::joinFormulir()->open()->approvalRejected()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_downpayment->get()->count() > 5) {
            array_push($array, [
                'url' => url('expedition/point/downpayment/vesa-rejected'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.expedition.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('expedition/point/downpayment/' . $downpayment->id.'/edit'),
                'deadline' => $downpayment->formulir->form_date,
                'message' => $downpayment->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.expedition.downpayment'
            ]);
        }

        return $array;
    }
}
