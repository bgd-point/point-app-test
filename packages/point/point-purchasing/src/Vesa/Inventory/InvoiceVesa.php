<?php

namespace Point\PointPurchasing\Vesa\Inventory;

use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\Invoice;

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

    private static function vesaCreate($array=[], $merge_into_group = true)
    {
        $list_goods_received = GoodsReceived::joinFormulir()->open()->notArchived()->selectOriginal()->orderByStandard()->paginate(100);

        // Grouping vesa
        if ($merge_into_group && $list_goods_received->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/invoice/vesa-create'),
                'deadline' => $list_goods_received->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Please create invoice',
                'permission_slug' => 'create.point.purchasing.invoice'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_goods_received as $goods_received) {
            array_push($array, [
                'url' => url('purchasing/point/invoice/create-step-2/' . $goods_received->id),
                'deadline' => $goods_received->formulir->form_date,
                'message' => 'Please create invoice from ' . $goods_received->formulir->form_number,
                'permission_slug' => 'create.point.purchasing.invoice'
            ]);
        }

        return $array;
    }
}
