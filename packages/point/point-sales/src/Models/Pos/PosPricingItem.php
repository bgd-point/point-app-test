<?php
namespace Point\PointSales\Models\Pos;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\ByTrait;

class PosPricingItem extends Model
{
    protected $table = 'point_sales_pos_pricing_item';
    public $timestamps = false;

    use ByTrait;

    public function scopeJoinPosPricing($q)
    {
        $q->join('point_sales_pos_pricing', 'point_sales_pos_pricing.id', '=', 'point_sales_pos_pricing_item.pos_pricing_id');
    }

    public function posPricing()
    {
        return $this->belongsTo('Point\PointSales\Models\Pos\PosPricing', 'pos_pricing_id');
    }

    public function item()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Item', 'item_id');
    }

    public function personGroup()
    {
        return $this->belongsTo('Point\Framework\Models\Master\PersonGroup', 'person_group_id');
    }
}
