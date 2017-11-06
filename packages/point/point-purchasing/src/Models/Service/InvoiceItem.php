<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class InvoiceItem extends Model
{
    use ByTrait;

    protected $table = 'point_purchasing_service_invoice_item';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'item_id');
    }
    public function scopeJoinAllocation($q){
        $q->join('allocation', 'allocation.id', '=', $this->table.'.allocation_id');
    }
    public function scopeJoinItem($q){
        $q->join('item', 'item.id', '=', $this->table.'.item_id');
    }
    
    public function scopeJoinServiceInvoice($q){
        $q->join('point_purchasing_service_invoice', 'point_purchasing_service_invoice.id', '=', $this->table.'.point_purchasing_service_invoice_id');
    }

    
    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_purchasing_service_invoice.formulir_id');
    }
}
