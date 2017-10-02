<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;

class ExpeditionOrderGroupDetail extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_order_group_detail';

    public function group()
    {
        return $this->belongsTo('\Point\PointExpedition\Models\ExpeditionOrderGroup', 'point_expedition_order_group_id');
    }
}