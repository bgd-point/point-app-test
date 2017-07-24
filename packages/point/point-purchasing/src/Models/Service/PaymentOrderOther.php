<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;

class PaymentOrderOther extends Model
{
    protected $table = 'point_purchasing_service_payment_order_other';
    
    public $timestamps = false;

    public function scopeJoinPaymentOrder($q)
    {
        $q->join('point_purchasing_service_payment_order', 'point_purchasing_service_payment_order.id', '=', 'point_purchasing_service_payment_order_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_purchasing_order.formulir_id');
    }

    public function paymentOrder()
    {
        return $this->belongsTo('\Point\PointPurchasing\Models\Service\PaymentOrder', 'point_purchasing_service_payment_order_id');
    }

    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }
}
