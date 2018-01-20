<?php

namespace Point\Framework\Helpers;

class ClientHelper
{
    public static function hasAddon($code)
    {
        if (env('SERVER_DOMAIN') == 'point.app') {
            return true;
        }

        $addons = request('addons');
        if ($code == 'basic' || $code == 'pro' || $code == 'premium') {
            return self::checkPackage($code, $addons);
        }

        foreach ($addons as $addon) {
            if ($addon->code == $code) {
                return true;
            }
        }

        return false;
    }

    private static function checkPackage($code, $addons)
    {
        foreach ($addons as $addon) {
            // if user has basic package | TIER 1
            if ($addon->code == $code) {
                return true;
            }

            // if user has pro package | TIER 2
            if ($addon->code == 'pro' && ($code == 'pro' || $code == 'basic')) {
                return true;
            }

            // if user has premium package | TIER 3
            if ($addon->code == 'premium' && ($code == 'premium' || $code == 'pro' || $code == 'basic')) {
                return true;
            }
        }

        return false;
    }
}
