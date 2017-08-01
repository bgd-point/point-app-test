<?php

namespace Point\PointFinance\Models\Cheque;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class Cheque extends Model
{
    protected $table = 'point_finance_cheque';
    public $timestamps = false;

    use FormulirTrait;

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
        $q->join('person', 'person.id', '=', 'point_finance_cheque.person_id');
    }

    public function detail()
    {
        return $this->hasMany('Point\PointFinance\Models\Cheque\ChequeDetailPayment', 'point_finance_cheque_id');
    }

    public function detailCheque()
    {
        return $this->hasMany('Point\PointFinance\Models\Cheque\ChequeDetail', 'point_finance_cheque_id');
    }

    public function person()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Person', 'person_id');
    }

    public function account()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public static function showUrl($id)
    {
        $cheque = Cheque::find($id);
        if ($cheque->formulir->form_number) {
            return '/finance/point/cheque/' . $cheque->payment_flow . '/' . $cheque->id;
        } else {
            return '/finance/point/cheque/' . $cheque->payment_flow . '/' . $cheque->id . '/archived';
        }
    }
}
