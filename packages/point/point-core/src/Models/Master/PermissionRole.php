<?php

namespace Point\Core\Models\Master;

use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    protected $table = 'permission_role';
     
    public static function check($role_id, $permission_id)
    {
        return PermissionRole::where('role_id', '=', $role_id)->where('permission_id', '=', $permission_id)->count() > 0;
    }
}
