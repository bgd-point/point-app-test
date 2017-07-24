<?php

namespace Point\PointInventory\Vesa;

trait StockOpnameVesa
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
        $list_stock_opname = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_stock_opname->count() > 5) {
            array_push($array, [
                'url' => url('inventory/point/stock-opname/vesa-approval'),
                'deadline' => $list_stock_opname->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve this stock opname',
                'permission_slug' => 'approval.point.inventory.stock.opname'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_stock_opname->get() as $stock_opname) {
            array_push($array, [
                'url' => url('inventory/point/stock-opname/' . $stock_opname->id),
                'deadline' => $stock_opname->formulir->form_date,
                'message' => 'please approve this stock opname ' . $stock_opname->formulir->form_number,
                'permission_slug' => 'approval.point.inventory.stock.opname'
            ]);
        }

        return $array;
    }

    private static function VesaRejected($array = [], $merge_into_group = true)
    {
        $list_stock_opname = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_stock_opname->count() > 5) {
            array_push($array, [
                'url' => url('inventory/point/stock-opname/vesa-rejected'),
                'deadline' => $list_stock_opname->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.inventory.stock.opname'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_stock_opname->get() as $stock_opname) {
            array_push($array, [
                'url' => url('inventory/point/stock-opname/' . $stock_opname->id.'/edit'),
                'deadline' => $stock_opname->required_date ? : $stock_opname->formulir->form_date,
                'message' => $stock_opname->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.inventory.stock.opname'
            ]);
        }

        return $array;
    }
}
