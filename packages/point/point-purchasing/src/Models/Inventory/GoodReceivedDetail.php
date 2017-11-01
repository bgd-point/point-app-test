<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class GoodReceivedDetail extends Model
{
    use ByTrait;

    protected $table = 'point_purchasing_good_received_item';
    public $timestamps = false;

    public function scopeJoinAllocation($q){
        $q->join('allocation', 'allocation.id', '=', 'point_purchasing_good_received_item.allocation_id');
    }
    public function scopeJoinItem($q){
        $q->join('item', 'item.id', '=', 'point_purchasing_good_received_item.item_id');
    }
    //join from detail purchase to header purchase
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
