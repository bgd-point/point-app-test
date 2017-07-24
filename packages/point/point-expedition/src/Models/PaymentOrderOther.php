<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentOrderOther extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_payment_order_other';

    public function scopeJoinPaymentOrder($q)
    {
        $q->join('point_expedition_payment_order', 'point_expedition_payment_order.id', '=',
            'point_expedition_payment_order_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_expedition_order.formulir_id');
    }

    public function paymentOrder()
    {
        return $this->belongsTo('\Point\PointExpedition\Models\PaymentOrder', 'point_expedition_payment_order_id');
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
