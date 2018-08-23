<?php

namespace Point\PointFinance\Models;

use Illuminate\Database\Eloquent\Model;
use Point\PointFinance\Vesa\PaymentVesa;
use Point\Framework\Traits\FormulirTrait;

class PaymentReference extends Model
{
    use PaymentVesa, FormulirTrait;

    protected $table = 'point_finance_payment_reference';
    public $timestamps = false;

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', $this->table.'.payment_reference_id');
    }

    public function scopeSelectOriginal($q)
    {
        $q->select([$this->table.'.*']);
    }
    
    public function scopeOrderByStandard($q)
    {
        $q->orderBy(\DB::raw('CAST(form_date as date)'), 'desc')
            ->orderBy(\DB::raw('SUBSTRING_INDEX(form_number, "/", -2)'));
    }

    public function reference()
    {
        return $this->belongsTo('Point\Framework\Models\Formulir', 'payment_reference_id');
    }

    public function person()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Person', 'person_id');
    }

    public function cashAdvance()
    {
        return $this->belongsTo('Point\PointFinance\Models\CashAdvance', 'cash_advance_id');
    }

    public function detail()
    {
        return $this->hasMany('Point\PointFinance\Models\PaymentReferenceDetail', 'point_finance_payment_reference_id');
    }
}
