<?php

namespace Point\PointSales\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    protected $table = 'point_sales_pos_item';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }
}
