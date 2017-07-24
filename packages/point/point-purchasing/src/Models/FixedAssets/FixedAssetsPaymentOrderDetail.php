<?php

namespace Point\PointPurchasing\Models\FixedAssets;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class FixedAssetsPaymentOrderDetail extends Model
{
    use FormulirTrait;

    protected $table = 'point_purchasing_fixed_assets_payment_order_detail';
    public $timestamps = false;

    public function paymentOrder()
    {
        return $this->belongsTo('\Point\PointPurchasing\Models\FixedAssets\FixedAssetsPaymentOrder', 'point_purchasing_fixed_assets_payment_order_id');
    }

    public function scopeJoinPaymentOrder($q)
    {
        $q->join('point_purchasing_fixed_assets_payment_order', 'point_purchasing_fixed_assets_payment_order.id', '=', 'point_purchasing_fixed_assets_payment_order_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_purchasing_fixed_assets_payment_order.formulir_id');
    }
}
