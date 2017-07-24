<?php

namespace Point\PointSales\Vesa;

use Point\PointSales\Models\Retur;
use Point\PointSales\Models\Sales\Invoice;

trait ReturVesa
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
        $list_retur = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_retur->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/retur/vesa-approval'),
                'deadline' => $list_retur->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve retur sales',
                'permission_slug' => 'approval.point.sales.return'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_retur->get() as $retur) {
            array_push($array, [
                'url' => url('sales/point/indirect/retur/' . $retur->id),
                'deadline' => $retur->required_date ? : $retur->formulir->form_date,
                'message' => 'Please approve this retur sales number ' . $retur->formulir->form_number,
                'permission_slug' => 'approval.point.sales.return'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_retur = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_retur->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/retur/vesa-rejected'),
                'deadline' => $list_retur->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form retur sales',
                'permission_slug' => 'update.point.sales.return'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_retur->get() as $retur) {
            array_push($array, [
                'url' => url('sales/point/indirect/retur/' . $retur->id.'/edit'),
                'deadline' => $retur->required_date ? : $retur->formulir->form_date,
                'message' => $retur->formulir->form_number. ' Rejected, please edit your form retur sales',
                'permission_slug' => 'update.point.sales.return'
            ]);
        }

        return $array;
    }
}
