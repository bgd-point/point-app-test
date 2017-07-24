<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class InvoiceItem extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_invoice_item';

    use ByTrait;

    public function scopeJoinItem($q)
    {
        $q->join('item', 'item.id', '=', 'point_expedition_invoice_item.item_id');
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
