<?php

namespace Point\PointSales\Models\Service;

use Illuminate\Database\Eloquent\Model;

class PaymentCollectionOther extends Model
{
    protected $table = 'point_sales_service_payment_collection_other';
    public $timestamps = false;

    public function scopeJoinPaymentCollection($q)
    {
        $q->join('point_sales_service_payment_collection', 'point_sales_service_payment_collection.id', '=', 'point_sales_service_payment_collection_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_sales_order.formulir_id');
    }
    public function scopeJoinCoa($q)
    {
        $q->join('coa', 'coa.id', '=','point_sales_service_payment_collection_other.coa_id');
    }
    public function paymentOrder()
    {
        return $this->belongsTo('\Point\PointSales\Models\Service\PaymentCollection', 'point_sales_service_payment_collection_id');
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
