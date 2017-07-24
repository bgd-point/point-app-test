<?php

namespace Point\PointPurchasing\Vesa\Inventory;

use Point\PointPurchasing\Models\Inventory\Invoice;
use Point\PointPurchasing\Models\Inventory\Retur;

trait ReturVesa
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

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_retur = self::joinFormulir()->open()->notArchived()->approvalPending()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_retur->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/retur/vesa-approval'),
                'deadline' => $list_retur->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve retur purchasing',
                'permission_slug' => 'approval.point.purchasing.return'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_retur->get() as $retur) {
            array_push($array, [
                'url' => url('purchasing/point/retur/' . $retur->id),
                'deadline' => $retur->required_date ? : $retur->formulir->form_date,
                'message' => 'Please approve this retur purchasing number ' . $retur->formulir->form_number,
                'permission_slug' => 'approval.point.purchasing.return'
            ]);
        }

        return $array;
    }

    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $list_invoice = Invoice::joinFormulir()
            ->notArchived()
            ->approvalApproved()
            ->open()
            ->orderByStandard()
            ->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_invoice->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/retur/vesa-create'),
                'deadline' => $list_invoice->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Make a retur from invoice',
                'permission_slug' => 'create.point.purchasing.return'
            ]);
            return $array;
        }
        // Push all
        foreach ($list_invoice->get() as $invoice) {
            array_push($array, [
                'url' => url('purchasing/point/retur/create-step-2/' . $invoice->id),
                'deadline' => $invoice->required_date ? : $invoice->formulir->form_date,
                'message' => 'Make a retur from invoice number ' . $invoice->formulir->form_number,
                'permission_slug' => 'create.point.purchasing.return'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_retur = self::joinFormulir()->open()->notArchived()->approvalRejected()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_retur->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/retur/vesa-rejected'),
                'deadline' => $list_retur->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form retur purchasing',
                'permission_slug' => 'update.point.purchasing.return'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_retur->get() as $retur) {
            array_push($array, [
                'url' => url('purchasing/point/retur/' . $retur->id.'/edit'),
                'deadline' => $retur->required_date ? : $retur->formulir->form_date,
                'message' => $retur->formulir->form_number. ' Rejected, please edit your form retur purchasing',
                'permission_slug' => 'update.point.purchasing.return'
            ]);
        }

        return $array;
    }
}
