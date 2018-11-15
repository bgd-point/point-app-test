<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class PurchaseOrderDetail extends Model
{
    use ByTrait, FormulirTrait, ServicePaymentOrderVesa;

    protected $table = 'point_purchasing_service_purchase_order_detail';
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
