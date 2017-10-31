<?php

namespace Point\PointFinance\Models\PaymentOrder;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointFinance\Vesa\PaymentOrderVesa;

class PaymentOrder extends Model
{
    protected $table = 'point_finance_payment_order';
    public $timestamps = false;

    use FormulirTrait, PaymentOrderVesa;

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

    public function scopeSearch($q)
    {
        if (app('request')->input('date_from')) {
            $q->where('form_date', '>=', \DateHelper::formatDB(app('request')->input('date_from'), 'start'));
        }

        if (app('request')->input('date_to')) {
            $q->where('form_date', '<=', \DateHelper::formatDB(app('request')->input('date_to'), 'end'));
        }

        if (app('request')->input('search')) {
            $q->where('form_number', 'like', '%'.app('request')->input('search').'%');
        }
    }

    public function detail()
    {
        return $this->hasMany('Point\PointFinance\Models\PaymentOrder\PaymentOrderDetail', 'point_finance_payment_order_id');
    }

    public function cashAdvance()
    {
        return $this->belongsTo('Point\PointFinance\Models\CashAdvance', 'cash_advance_id');
    }

    public function person()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Person', 'person_id');
    }

    public static function bladeEmail()
    {
        return 'point-finance::emails.finance.point.approval.payment-order';
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return 'finance/point/payment-order/' . $id;
        }

        return 'finance/point/payment-order/' . $id . '/archived';
    }
}
