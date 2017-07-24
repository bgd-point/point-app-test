<?php

namespace Point\Core\Helpers;

use Point\Core\Models\User;

class UserHelper
{

    /**
     * Get all user wihout default user "system"
     *
     * @return mixed
     */
    public static function getAllUser()
    {
        return User::where('id', '>', 1)->get();
    }
}
