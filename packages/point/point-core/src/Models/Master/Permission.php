<?php

namespace Point\Core\Models\Master;

class Permission extends \Bican\Roles\Models\Permission
{
    protected $table = 'permissions';

    /**
     * @param $q
     * @param $search
     * @return mixed
     */
    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%'.$search.'%');
    }
}
