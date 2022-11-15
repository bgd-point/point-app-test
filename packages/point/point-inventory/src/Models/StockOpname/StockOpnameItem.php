<?php

namespace Point\PointInventory\Models\StockOpname;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    protected $table = 'point_inventory_stock_opname_item';
    public $timestamps = false;
    
    public function opname()
    {
        return $this->belongsTo('Point\PointInventory\Models\StockOpname\StockOpname', 'stock_opname_id');
    }

    public function item()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Item', 'item_id');
    }

    public function itemUnit()
    {
        return $this->belongsTo('Point\Framework\Models\Master\ItemUnit', 'item_id');
    }
}
