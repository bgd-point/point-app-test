<?php

namespace Point\PointPurchasing\Models\Inventory\Basic;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class PaymentOrderDetail extends Model
{
    use FormulirTrait;

    protected $table = 'point_purchasing_basic_payment_order_detail';
    public $timestamps = false;

    public function paymentOrder()
    {
        return $this->belongsTo('\Point\PointPurchasing\Models\Inventory\Basic\PaymentOrder', 'point_purchasing_basic_payment_order_id');
    }

    public function scopeJoinPaymentOrder($q)
    {
        $q->join('point_purchasing_basic_payment_order', 'point_purchasing_basic_payment_order.id', '=', 'point_purchasing_payment_order_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_purchasing_basic_payment_order.formulir_id');
    }
}
