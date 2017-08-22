<?php

namespace Point\PointSales\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class InvoiceService extends Model
{
    use ByTrait;

    protected $table = 'point_sales_service_invoice_service';
    public $timestamps = false;

    public function scopeJoinInvoice($q)
    {
        $q->join('point_sales_service_invoice', 'point_sales_service_invoice.id', '=', 'point_sales_service_invoice_service.point_sales_service_invoice_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_sales_service_invoice.formulir_id');
    }

    public function service()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Service', 'service_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }
}
