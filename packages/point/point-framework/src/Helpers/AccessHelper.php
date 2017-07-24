<?php

namespace Point\Framework\Helpers;

use Point\Core\Exceptions\PointException;

class AccessHelper
{

    /**
     * @param $permission_slug
     *
     * @throws \Point\Core\Exceptions\PointException
     */
    public static function isAllowed($permission_slug)
    {
        if (! auth()->user()->may($permission_slug)) {
            throw new PointException('RESTRICTED PERMISSION ACCESS');
        }
    }

    public static function isAllowedToView($permission_slug)
    {
        if (! auth()->user()->may($permission_slug)) {
            return false;
        }

        return true;
    }
}
