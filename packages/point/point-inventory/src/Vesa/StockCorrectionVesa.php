<?php

namespace Point\PointInventory\Vesa;

trait StockCorrectionVesa
{
    public static function getVesa()
    {
        $array = self::vesaApproval();
        $array = self::vesaRejected($array);
        return $array;
    }

    public static function getVesaApproval()
    {
        return self::vesaApproval([], false);
    }

    public static function getVesaRejected()
    {
        return self::VesaRejected([], false);
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_stock_correction = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_stock_correction->count() > 5) {
            array_push($array, [
                'url' => url('inventory/point/stock-correction/vesa-approval'),
                'deadline' => $list_stock_correction->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve this stock correction',
                'permission_slug' => 'approval.point.inventory.stock.correction'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_stock_correction->get() as $stock_correction) {
            array_push($array, [
                'url' => url('inventory/point/stock-correction/' . $stock_correction->id),
                'deadline' => $stock_correction->formulir->form_date,
                'message' => 'please approve this stock correction ' . $stock_correction->formulir->form_number,
                'permission_slug' => 'approval.point.inventory.stock.correction'
            ]);
        }

        return $array;
    }

    private static function VesaRejected($array = [], $merge_into_group = true)
    {
        $list_stock_correction = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_stock_correction->count() > 5) {
            array_push($array, [
                'url' => url('inventory/point/stock-correction/vesa-rejected'),
                'deadline' => $list_stock_correction->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.inventory.stock.correction'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_stock_correction->get() as $stock_correction) {
            array_push($array, [
                'url' => url('inventory/point/stock-correction/' . $stock_correction->id.'/edit'),
                'deadline' => $stock_correction->required_date ? : $stock_correction->formulir->form_date,
                'message' => $stock_correction->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.inventory.stock.correction'
            ]);
        }

        return $array;
    }
}
