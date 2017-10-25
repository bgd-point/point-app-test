<?php

namespace Point\Core\Helpers;

use Point\Core\Models\Master\Permission;
use Point\Core\Models\Master\PermissionRole;

class PermissionHelper
{

    /**
     * @param $permission_type
     * @return mixed
     */
    public static function getPermissionByType($permission_type)
    {
        return Permission::where('type', '=', $permission_type)->get();
    }

    /**
     * @param $role_id
     * @param $permission_id
     * @return bool
     */
    public static function check($role_id, $permission_id)
    {
        return PermissionRole::where('role_id', '=', $role_id)->where('permission_id', '=', $permission_id)->count() > 0;
    }

    public static function checkAll($role_id, $permission_type)
    {
        foreach (permission_get_by_type($permission_type) as $permission) {
            if (permission_check($role_id, $permission->id) === false) {
                return false;
            }
        }

        return true;
    }

    public static function create($property, $permissions, $group)
    {
        foreach ($permissions as $permission) {
            switch ($permission) {
                case 'menu':
                    if (self::exist('menu.' . str_slug($property, '.'))) {
                        continue;
                    }

                    $permission = new Permission;
                    $permission->name = 'Menu ' . $property;
                    $permission->slug = 'menu.' . str_slug($property, '.');
                    $permission->group = $group;
                    $permission->type = '# Menu ' . $property;
                    $permission->action = 'Access Menu';
                    $permission->save();

                    break;
                case 'create':
                    if (self::exist('create.' . str_slug($property, '.'))) {
                        continue;
                    }

                    $permission = new Permission;
                    $permission->name = 'Create ' . $property;
                    $permission->slug = 'create.' . str_slug($property, '.');
                    $permission->group = $group;
                    $permission->type = $property;
                    $permission->action = 'Create';
                    $permission->save();

                    break;
                case 'read':
                    if (self::exist('read.' . str_slug($property, '.'))) {
                        continue;
                    }

                    $permission = new Permission;
                    $permission->name = 'Read ' . $property;
                    $permission->slug = 'read.' . str_slug($property, '.');
                    $permission->group = $group;
                    $permission->type = $property;
                    $permission->action = 'Read';
                    $permission->save();

                    break;
                case 'update':
                    if (self::exist('update.' . str_slug($property, '.'))) {
                        continue;
                    }

                    $permission = new Permission;
                    $permission->name = 'Update ' . $property;
                    $permission->slug = 'update.' . str_slug($property, '.');
                    $permission->group = $group;
                    $permission->type = $property;
                    $permission->action = 'Edit';
                    $permission->save();

                    break;
                case 'delete':
                    if (self::exist('delete.' . str_slug($property, '.'))) {
                        continue;
                    }

                    $permission = new Permission;
                    $permission->name = 'Delete ' . $property;
                    $permission->slug = 'delete.' . str_slug($property, '.');
                    $permission->group = $group;
                    $permission->type = $property;
                    $permission->action = 'Delete';
                    $permission->save();

                    break;
                case 'export':
                    if (self::exist('export.' . str_slug($property, '.'))) {
                        continue;
                    }

                    $permission = new Permission;
                    $permission->name = 'Export ' . $property;
                    $permission->slug = 'export.' . str_slug($property, '.');
                    $permission->group = $group;
                    $permission->type = $property;
                    $permission->action = 'Export';
                    $permission->save();

                    break;
                case 'approval':
                    if (self::exist('approval.' . str_slug($property, '.'))) {
                        continue;
                    }

                    $permission = new Permission;
                    $permission->name = 'Approval ' . $property;
                    $permission->slug = 'approval.' . str_slug($property, '.');
                    $permission->group = $group;
                    $permission->type = $property;
                    $permission->action = 'Approval';
                    $permission->save();

                    break;
                case 'manage':
                    if (self::exist('manage.' . str_slug($property, '.'))) {
                        continue;
                    }

                    $permission = new Permission;
                    $permission->name = 'Manage ' . $property;
                    $permission->slug = 'manage.' . str_slug($property, '.');
                    $permission->group = $group;
                    $permission->type = $property;
                    $permission->action = 'Manage';
                    $permission->save();

                    break;
            }
        }
    }

    private static function exist($property)
    {
        return Permission::where('slug', '=', $property)->first();
    }
}
