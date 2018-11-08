<?php

namespace Point\Core\Models;

use Bican\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;
use Bican\Roles\Traits\HasRoleAndPermission;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Point\Core\Models\Master\PermissionRole;
use Point\Core\Models\Master\RoleUser;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;


class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    HasRoleAndPermissionContract
{
    use Authenticatable, Authorizable, CanResetPassword, HasRoleAndPermission {
        Authorizable::can insteadof HasRoleAndPermission;
        HasRoleAndPermission::can as may;
    }

    use HistoryTrait, ByTrait, MasterTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @param $user_id
     * @return mixed
     */
    public static function allRoles($user_id)
    {
        return RoleUser::where('user_id', '=', $user_id)->get();
    }

    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%' . $search . '%')->where('id', '>', 1);
    }

    public function checkRole($permission_id)
    {
        $roleUsers = RoleUser::where('user_id', '=', $this->id)->get();
        foreach ($roleUsers as $roleUser) {
            if (PermissionRole::check($roleUser->role_id, $permission_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int|string $role_id
     * @return bool
     */
    public function hasRole($role_id)
    {
        if (RoleUser::where('role_id', '=', $role_id)->where('user_id', '=', $this->id)->get()->count() > 0) {
            return true;
        }
        return false;
    }
}
