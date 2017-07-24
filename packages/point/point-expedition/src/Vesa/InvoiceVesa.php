<?php

namespace Point\PointExpedition\Vesa;

use Point\PointExpedition\Models\ExpeditionOrder;

trait InvoiceVesa
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
        $list_single_expedition = ExpeditionOrder::joinFormulir()->approvalApproved()->open()->notArchived()->selectOriginal()->orderByStandard();
        $list_expedition_order_group = ExpeditionOrder::joinFormulir()->availableToInvoiceGroupExpedition()->selectOriginal()->get();
        // Grouping vesa
        if ($merge_into_group && $list_single_expedition->get()->count() < 5) {
            foreach ($list_single_expedition->get() as $expedition_order) {
                array_push($array, [
                    'url' => url('expedition/point/invoice/create-step-2/'.$expedition_order->expedition_id),
                    'deadline' => $expedition_order->delivery_date ? : $expedition_order->formulir->form_date,
                    'message' => 'Make an expedition invoice from '.$expedition_order->formulir->form_number,
                    'permission_slug' => 'create.point.expedition.invoice'
                ]);
            }
            return $array;
        }

        // Grouping vesa
        if ($list_expedition_order_group->count() < 5) {
            foreach ($list_expedition_order_group as $expedition_order) {
                array_push($array, [
                    'url' => url('expedition/point/invoice/create-step-2/'.$expedition_order->expedition_id),
                    'deadline' => $expedition_order->delivery_date ? : $expedition_order->formulir->form_date,
                    'message' => 'Make an expedition invoice from '.$expedition_order->expedition->codeName,
                    'permission_slug' => 'create.point.expedition.invoice'
                ]);
            }
            return $array;
        }

        // Push all
        foreach ($list_single_expedition as $expedition_order) {
            array_push($array, [
                'url' => url('expedition/point/invoice/create-step-1'),
                'deadline' => $expedition_order->delivery_date ? : $expedition_order->formulir->form_date,
                'message' => 'Make an expedition invoice',
                'permission_slug' => 'create.point.expedition.invoice'
            ]);
        }

        return $array;
    }
}
