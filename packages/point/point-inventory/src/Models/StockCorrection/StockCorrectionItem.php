<?php

namespace Point\PointInventory\Models\StockCorrection;

use Illuminate\Database\Eloquent\Model;

class StockCorrectionItem extends Model
{
    protected $table = 'point_inventory_stock_correction_item';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Item', 'item_id');
    }

    public function itemUnit()
    {
        return $this->belongsTo('Point\Framework\Models\Master\ItemUnit', 'item_id');
    }
}
