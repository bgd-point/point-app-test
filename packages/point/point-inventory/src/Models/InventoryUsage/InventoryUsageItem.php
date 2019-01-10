<?php

namespace Point\PointInventory\Models\InventoryUsage;

use Illuminate\Database\Eloquent\Model;

class InventoryUsageItem extends Model
{
    protected $table = 'point_inventory_usage_item';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Item', 'item_id');
    }

    public function allocation()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Allocation', 'allocation_id');
    }

    public function itemUnit()
    {
        return $this->belongsTo('Point\Framework\Models\Master\ItemUnit', 'item_id');
    }
    public function coa()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Coa', 'coa_id');
    }
}
