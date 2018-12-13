<?php

namespace Point\PointExpedition\Vesa;

use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderReference;

trait ExpeditionOrderVesa
{
    public static function getVesa()
    {
        $array = self::vesaCreate();
        $array = self::vesaApproval($array);
        $array = self::vesaReject($array);
        return [];
//        return $array;
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
        $list_expedition_order = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_expedition_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('expedition/point/expedition-order/vesa-approval'),
                'deadline' => $list_expedition_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve expedition order',
                'permission_slug' => 'approval.point.expedition.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_expedition_order->get() as $expedition_order) {
            array_push($array, [
                'url' => url('expedition/point/expedition-order/' . $expedition_order->id),
                'deadline' => $expedition_order->delivery_date ? : $expedition_order->formulir->form_date,
                'message' => 'please approve this expedition order ' . $expedition_order->formulir->form_number,
                'permission_slug' => 'approval.point.expedition.order'
            ]);
        }

        return $array;
    }

    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $array_expedition_reference_id_open = ExpeditionOrder::getExpeditionReferenceIsOpen();
        $list_expedition_reference = ExpeditionOrderReference::joinFormulir()->joinPerson()->whereIn('expedition_reference_id', $array_expedition_reference_id_open)->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_expedition_reference->get()->count() > 5) {
            array_push($array, [
                'url' => url('expedition/point/expedition-order/vesa-create'),
                'deadline' => $list_expedition_reference->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'create expedition order',
                'permission_slug' => 'create.point.expedition.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_expedition_reference->get() as $expedition_reference) {
            array_push($array, [
                'url' => url('expedition/point/expedition-order/create-step-2/' . $expedition_reference->expedition_reference_id),
                'deadline' => $expedition_reference->formulir->form_date,
                'message' => 'create expedition order ' . formulir_url($expedition_reference->formulir),
                'permission_slug' => 'create.point.expedition.order'
            ]);
        }

        return $array;
    }


    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_expedition_order = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_expedition_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('expedition/point/expedition-order/vesa-rejected'),
                'deadline' => $list_expedition_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.expedition.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_expedition_order->get() as $expedition_order) {
            array_push($array, [
                'url' => url('expedition/point/expedition-order/' . $expedition_order->id.'/edit'),
                'deadline' => $expedition_order->delivery_date ? : $expedition_order->formulir->form_date,
                'message' => $expedition_order->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.expedition.order'
            ]);
        }

        return $array;
    }
}
