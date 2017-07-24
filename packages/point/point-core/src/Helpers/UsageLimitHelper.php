<?php

namespace Point\Core\Helpers;

use Illuminate\Support\Facades\Storage;
use Point\Core\Models\User;

class UsageLimitHelper
{
    public static function storageLimit()
    {
        $data['current_size'] = self::currentStorageSize();
        $data['max_size'] = env('CLIENT_MAX_STORAGE') ?: config('point.client.max_storage');

        if ($data['current_size'] == 0) {
            $data['percent_storage'] = 0;
        } else {
            $data['percent_storage'] = $data['current_size'] / $data['max_size'] * 100;
        }

        return $data;
    }

    private static function currentStorageSize()
    {
        $storages = Storage::allFiles('client/' . config('point.client.slug'));
        $size = 0;
        foreach ($storages as $storage) {
            $size += Storage::size($storage);
        }
        return $size;
    }

    public static function userLimit()
    {
        $data['current_active_user'] = User::where('disabled', '=', false)->count() - 1;
        $data['max_active_user'] = env('CLIENT_MAX_USER') ?: config('point.client.max_user');

        if ($data['current_active_user'] == 0) {
            $data['percent_user'] = 0;
        } else {
            $data['percent_user'] = $data['current_active_user'] / $data['max_active_user'] * 100;
        }
        return $data;
    }
}
