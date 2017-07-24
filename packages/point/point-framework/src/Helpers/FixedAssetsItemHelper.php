<?php

namespace Point\Framework\Helpers;

use Point\Core\Exceptions\PointException;
use Point\Framework\Models\Master\FixedAssetsItem;

class FixedAssetsItemHelper
{

    /**
     * @param $permission_slug
     *
     * @throws \Point\Core\Exceptions\PointException
     */
    public static function isAvailable()
    {
        if (!FixedAssetsItem::all()->count()) {
            throw new PointException('NO FIXED ASSETS DETECTED, PLEASE CREATE AT LEAST ONE FIXED ASSETS');
        }
    }

    public static function getLastCode()
    {
        $last_fixed_assets_item = FixedAssetsItem::orderBy('id', 'desc')->first();
        $new_code = 1;
        if ($last_fixed_assets_item) {
            $new_code = (int)str_replace('FA-', '', $last_fixed_assets_item->code);
            $new_code += 1;
        }

        return 'FA-' . ($new_code);
    }
}
