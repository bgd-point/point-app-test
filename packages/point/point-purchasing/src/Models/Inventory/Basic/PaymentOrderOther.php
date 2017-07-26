<?php

namespace Point\PointPurchasing\Models\Inventory\Basic;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class PaymentOrderOther extends Model
{
    use FormulirTrait;
    protected $table = 'point_purchasing_basic_payment_order_other';
    public $timestamps = false;

    public function scopeJoinPaymentOrder($q)
    {
        $q->join('point_purchasing_basic_payment_order', 'point_purchasing_basic_payment_order.id', '=', 'point_purchasing_basic_payment_order_id');
    }

    public function paymentOrder()
    {
        return $this->belongsTo('\Point\PointPurchasing\Models\Inventory\Basic\PaymentOrder', 'point_purchasing_basic_payment_order_id');
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
