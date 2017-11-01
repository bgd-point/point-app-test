<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class GoodsReceivedItem extends Model
{
    use ByTrait;

    protected $table = 'point_purchasing_goods_received_item';
    public $timestamps = false;


    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'item_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }

    public function scopeJoinAllocation($q){
        $q->join('allocation', 'allocation.id', '=', 'point_purchasing_good_received_item.allocation_id');
    }
    public function scopeJoinItem($q){
        $q->join('item', 'item.id', '=', 'point_purchasing_good_received_item.item_id');
    }
    public function scopeJoinGoodReceived($q){
        $q->join('point_purchasing_order', 'point_purchasing_order.id', '=', 'point_purchasing_good_received_item.point_purchasing_good_received_id');
    }

    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_order.supplier_id');
    }
    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_purchasing_order.formulir_id');
    }
}
