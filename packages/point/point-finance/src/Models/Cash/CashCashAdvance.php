<?php 

namespace Point\PointFinance\Models\Cash;

use Point\Framework\Models\Formulir;

use Illuminate\Database\Eloquent\Model;

use Point\Framework\Traits\FormulirTrait;

class CashCashAdvance extends Model
{
    protected $table = 'point_finance_cash_cash_advance';
    public $timestamps = false;

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_finance_cash_id');
    }

    public function scopeNotArchived($q, $form_number = 0)
    {
        $q->whereNotNull('formulir.form_number');
        if ($form_number) {
            $q->where('formulir.form_number', '=', $form_number);
        }
    }

    public function scopeNotCanceled($q)
    {
        $q->where('formulir.form_status', '!=', -1);
    }

    public function scopeSelectOriginal($q)
    {
        $q->select([$this->table.'.*']);
    }

    public function scopeJoinCash($q)
    {
        $q->join('point_finance_cash', 'point_finance_cash.id', '=', $this->table.'.point_finance_cash_id');
    }

    public function used()
    {
        return $this->belongsTo('Point\Framework\Models\Formulir', 'point_finance_cash_id');
    }

    public function cashAdvance()
    {
        return $this->belongsTo('Point\PointFinance\Models\CashAdvance', 'cash_advance_id');
    }
}
