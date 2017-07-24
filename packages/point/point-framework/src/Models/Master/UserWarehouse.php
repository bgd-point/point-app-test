<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

class UserWarehouse extends Model
{
    protected $table = 'user_warehouse';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('\Point\Code\Models\User', 'user_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public static function getWarehouse($user_id)
    {
        $user_warehouse =  UserWarehouse::where('user_id', '=', $user_id)->first();
        if (! $user_warehouse) {
            return 0;
        }

        return $user_warehouse->warehouse_id;
    }
}
