<?php

namespace Point\PointInventory\Models\TransferItem;

use Illuminate\Database\Eloquent\Model;

class TransferItemDetail extends Model
{
    protected $table = 'point_inventory_transfer_item_detail';
    public $timestamps = false;

    public function transferitem()
    {
        return $this->belongsTo('Point\PointInventory\Models\TransferItem\TransferItem', 'transfer_item_id');
    }

    public function item()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Item', 'item_id');
    }
}
