<?php

namespace Point\PointPurchasing\Models\FixedAssets;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\FixedAssets\FixedAssetsPaymentOrderVesa;

class FixedAssetsPaymentOrder extends Model
{
    use ByTrait, FormulirTrait, FixedAssetsPaymentOrderVesa;

    protected $table = 'point_purchasing_fixed_assets_payment_order';
    public $timestamps = false;

    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_fixed_assets_payment_order.supplier_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function details()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\FixedAssets\FixedAssetsPaymentOrderDetail', 'point_purchasing_payment_order_id');
    }

    public function others()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\FixedAssets\FixedAssetsPaymentOrderOther', 'point_purchasing_payment_order_id');
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.payment-order';
    }
}
