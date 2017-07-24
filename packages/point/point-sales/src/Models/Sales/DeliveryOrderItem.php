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
}
