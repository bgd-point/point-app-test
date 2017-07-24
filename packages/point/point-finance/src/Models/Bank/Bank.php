<?php

namespace Point\PointFinance\Models\Bank;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class Bank extends Model
{
    protected $table = 'point_finance_bank';
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
        $q->join('person', 'person.id', '=', 'point_finance_bank.person_id');
    }

    public function detail()
    {
        return $this->hasMany('Point\PointFinance\Models\Bank\BankDetail', 'point_finance_bank_id');
    }

    public function person()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Person', 'person_id');
    }

    public function account()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }
}
