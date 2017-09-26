<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class InvoiceItem extends Model
{
    use ByTrait;

    protected $table = 'point_purchasing_invoice_item';
    public $timestamps = false;

    public function scopeJoinInvoice($q)
    {
        $q->join('point_purchasing_invoice', 'point_purchasing_invoice.id', '=', 'point_purchasing_invoice_item.point_purchasing_invoice_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_purchasing_invoice.formulir_id');
    }

    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_invoice.supplier_id');
    }

    public function invoice()
    {
        return $this->belongsTo('Point\PointPurchasing\Models\Inventory\Invoice', 'point_purchasing_invoice_id');
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
