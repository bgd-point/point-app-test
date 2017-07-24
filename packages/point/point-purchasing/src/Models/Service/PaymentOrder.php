<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\Service\ServicePaymentOrderVesa;

class PaymentOrder extends Model
{
    use ByTrait, FormulirTrait, ServicePaymentOrderVesa;

    protected $table = 'point_purchasing_service_payment_order';
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

    public function scopeJoinPerson($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_service_payment_order.person_id');
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public function details()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Service\PaymentOrderDetail', 'point_purchasing_service_payment_order_id');
    }

    public function others()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Service\PaymentOrderOther', 'point_purchasing_service_payment_order_id');
    }

    public static function showUrl($id)
    {
        if ($this->formulir->form_number) {
            return '/purchasing/point/service/payment-order/'.$this->id;
        } else {
            return '/purchasing/point/service/payment-order/'.$this->id.'/archived';
        }
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.service-payment-order';
    }
}
