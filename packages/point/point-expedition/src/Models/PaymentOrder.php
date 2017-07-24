<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointExpedition\Vesa\PaymentOrderVesa;

class PaymentOrder extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_payment_order';

    use ByTrait, FormulirTrait, PaymentOrderVesa;

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

    public function scopeJoinExpedition($q)
    {
        $q->join('person', 'person.id', '=', 'point_expedition_payment_order.expedition_id');
    }

    public function expedition()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'expedition_id');
    }

    public function details()
    {
        return $this->hasMany('\Point\PointExpedition\Models\PaymentOrderDetail', 'point_expedition_payment_order_id');
    }

    public function others()
    {
        return $this->hasMany('\Point\PointExpedition\Models\PaymentOrderOther', 'point_expedition_payment_order_id');
    }

    public static function showUrl()
    {
        if ($this->formulir->form_number) {
            return '/expedition/point/payment-order/' . $this->id;
        }

        return '/expedition/point/payment-order/' . $this->id . '/archived';
    }

    public static function bladeEmail()
    {
        return 'point-expedition::emails.expedition.point.approval.payment-order';
    }
}
