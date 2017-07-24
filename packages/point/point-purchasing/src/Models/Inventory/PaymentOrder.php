<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\Inventory\PaymentOrderVesa;

class PaymentOrder extends Model
{
    use ByTrait, FormulirTrait, PaymentOrderVesa;

    protected $table = 'point_purchasing_payment_order';
    public $timestamps = false;

    /**
     * Inject function when saving
     *
     * @param array $options
     *
     * @return bool|null
     */
    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }
    
    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_payment_order.supplier_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function details()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Inventory\PaymentOrderDetail', 'point_purchasing_payment_order_id');
    }

    public function others()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Inventory\PaymentOrderOther', 'point_purchasing_payment_order_id');
    }

    public static function showUrl()
    {
        if ($this->formulir->form_number) {
            return '/purchasing/point/payment-order/'.$id;
        }

        return '/purchasing/point/payment-order/'.$id.'/archived';
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.payment-order';
    }
}
