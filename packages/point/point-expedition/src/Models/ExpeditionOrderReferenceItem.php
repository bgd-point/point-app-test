<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class ExpeditionOrderReferenceItem extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_order_reference_item';

    use ByTrait;

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'item_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }

    public function expeditionReference()
    {
        return $this->belongsTo('Point\PointExpedition\Models\ExpeditionOrderReference', 'point_expedition_order_reference_id');
    }
}
