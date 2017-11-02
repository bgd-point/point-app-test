<?php

namespace Point\PointSales\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class InvoiceItem extends Model
{
    use ByTrait;

    protected $table = 'point_sales_service_invoice_item';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'item_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }
    public function scopeJoinItem($q){
        $q->join('item', 'item.id', '=', $this->table.'.item_id');
    }
    public function scopeJoinInvoice($q)
    {
        $q->join('point_sales_service_invoice', 'point_sales_service_invoice.id', '=', 'point_sales_service_invoice_item.point_sales_service_invoice_id');
    }
}
