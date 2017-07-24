<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class PaymentOrderDetail extends Model
{
    use FormulirTrait;

    protected $table = 'point_purchasing_service_payment_order_detail';
    public $timestamps = false;

    public function paymentOrder()
    {
        return $this->belongsTo('\Point\PointPurchasing\Models\Service\PaymentOrder', 'point_purchasing_service_payment_order_id');
    }

    public function scopeJoinPaymentOrder($q)
    {
        $q->join('point_purchasing_service_payment_order', 'point_purchasing_service_payment_order.id', '=', 'point_purchasing_service_payment_order_id');
    }
}
