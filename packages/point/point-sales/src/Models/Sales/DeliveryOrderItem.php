<?php

namespace Point\PointSales\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class DeliveryOrderItem extends Model
{
    use ByTrait;

    protected $table = 'point_sales_delivery_order_item';
    public $timestamps = false;

    public function parent()
    {
        return $this->belongsTo('Point\PointSales\Models\Sales\DeliveryOrder', 'point_sales_delivery_order_id');
    }

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'item_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }
    public function scopeJoinAllocation($q){
        $q->join('allocation', 'allocation.id', '=', $this->table.'.allocation_id');
    }
    public function scopeJoinItem($q){
        $q->join('item', 'item.id', '=', $this->table.'.item_id');
    }
    
    public function scopeJoinDeliveryOrder($q){
        $q->join('point_sales_delivery_order', 'point_sales_delivery_order.id', '=', $this->table.'.point_sales_delivery_order_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_sales_delivery_order.formulir_id');
    }
}
