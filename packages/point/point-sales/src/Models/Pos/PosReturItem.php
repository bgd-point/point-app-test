<?php

namespace Point\PointSales\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class PosReturItem extends Model
{
    protected $table = 'point_sales_pos_retur_item';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'item_id');
    }

    public function posRetur()
    {
        return $this->belongsTo('\Point\Sales\Models\Pos\PosRetur', 'pos_retur_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }
}
