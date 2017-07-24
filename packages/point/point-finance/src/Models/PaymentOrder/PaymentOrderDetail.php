<?php

namespace Point\PointFinance\Models\PaymentOrder;

use Illuminate\Database\Eloquent\Model;

class PaymentOrderDetail extends Model
{
    protected $table = 'point_finance_payment_order_detail';
    public $timestamps = false;

    public function paymentOrder()
    {
        return $this->belongsTo('Point\PointFinance\Models\PaymentOrder\PaymentOrder', 'point_finance_payment_order_id');
    }

    public function coa()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function coaCategory()
    {
        return $this->belongsTo('Point\Framework\Models\Master\CoaCategory', 'coa_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }

    public function assetRefer()
    {
        return $this->morphMany('\Point\PointAccounting\Models\AssetsRefer', 'payment');
    }
}
