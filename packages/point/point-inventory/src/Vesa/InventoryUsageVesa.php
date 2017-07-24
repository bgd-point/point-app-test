<?php

namespace Point\PointInventory\Vesa;

trait InventoryUsageVesa
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
        return self::vesaRejected([], false);
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_inventory_usage = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_inventory_usage->count() > 0) {
            array_push($array, [
                'url' => url('inventory/point/inventory-usage/vesa-approval'),
                'deadline' => $list_inventory_usage->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve this inventory usage',
                'permission_slug' => 'approval.point.inventory.usage'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_inventory_usage->get() as $inventory_usage) {
            array_push($array, [
                'url' => url('inventory/point/inventory-usage/' . $inventory_usage->id),
                'deadline' => $inventory_usage->formulir->form_date,
                'message' => 'please approve this inventory usage ' . $inventory_usage->formulir->form_number,
                'permission_slug' => 'approval.point.inventory.usage'
            ]);
        }

        return $array;
    }

    private static function vesaRejected($array = [], $merge_into_group = true)
    {
        $list_inventory_usage = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_inventory_usage->count() > 5) {
            array_push($array, [
                'url' => url('inventory/point/inventory-usage/vesa-rejected'),
                'deadline' => $list_inventory_usage->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.inventory.usage'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_inventory_usage->get() as $inventory_usage) {
            array_push($array, [
                'url' => url('inventory/point/inventory-usage/' . $inventory_usage->id.'/edit'),
                'deadline' => $inventory_usage->required_date ? : $inventory_usage->formulir->form_date,
                'message' => $inventory_usage->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.inventory.usage'
            ]);
        }

        return $array;
    }
}
