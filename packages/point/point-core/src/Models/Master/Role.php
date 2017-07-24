<?php

namespace Point\Core\Models\Master;

use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;

class Role extends \Bican\Roles\Models\Role
{
    protected $table = 'roles';

    use HistoryTrait, ByTrait;

    /**
     * @param string $value
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = \Str::slug($value, config('roles.separator'));

        $count = 0;
        while (\Point\Core\Models\Master\Role::where('slug', '=', $this->attributes['slug'])->count() > 0) {
            if (++$count > 1) {
                $string = explode('.', $this->attributes['slug']);
                $integer = end($string);
                ++$integer;
                array_pop($string);
                array_push($string, $integer);

                $this->attributes['slug'] = implode('.', $string);
            } else {
                $this->attributes['slug'] .= '.1';
            }
        }
    }

    /**
     * @param $permission_id
     * @return bool
     */
    public function checkRole($permission_id)
    {
        $permission = Permission::where('slug', '=', \Input::get('permission_slug'))->first();
        $roles = RoleUser::where('user_id', '=', $this->id)->get();
        foreach ($roles as $role) {
            if (PermissionRole::check($role->id, $permission->id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $q
     * @param $search
     * @return mixed
     */
    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%' . $search . '%');
    }
}
