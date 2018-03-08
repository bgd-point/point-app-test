<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class InvoiceService extends Model
{
    use ByTrait;

    protected $table = 'point_purchasing_service_invoice_service';
    public $timestamps = false;

    public function service()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Service', 'service_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }
}
