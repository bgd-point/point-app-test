<?php

namespace Point\Framework\Helpers;

use Point\Core\Exceptions\PointException;
use Point\Framework\Models\Master\Warehouse;

class WarehouseHelper
{

    /**
     * @param $permission_slug
     *
     * @throws \Point\Core\Exceptions\PointException
     */
    public static function isAvailable()
    {
        if (!Warehouse::all()->count()) {
            throw new PointException('NO WAREHOUSE DETECTED, PLEASE CREATE AT LEAST ONE WAREHOUSE');
        }
    }

    public static function getLastCode()
    {
        $last_warehouse = Warehouse::orderBy('id', 'desc')->first();
        $new_code = 1;
        if ($last_warehouse) {
            $new_code = (int)str_replace('WAREHOUSE-', '', $last_warehouse->code);
            $new_code += 1;
        }

        return 'WAREHOUSE-' . ($new_code);
    }
}
