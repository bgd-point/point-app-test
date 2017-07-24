<?php

namespace Point\Core\Models\Master;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $table = 'role_user';

    /**
     * @param $user_id
     * @param $role_id
     * @return bool
     */
    public static function check($user_id, $role_id)
    {
        return RoleUser::where('user_id', '=', $user_id)->where('role_id', '=', $role_id)->count() > 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('\Point\Core\Models\User');
    }
}
