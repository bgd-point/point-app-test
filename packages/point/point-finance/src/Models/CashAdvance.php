<?php

namespace Point\PointFinance\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointFinance\Vesa\CashAdvanceVesa;

class CashAdvance extends Model
{
    use ByTrait, FormulirTrait, CashAdvanceVesa;

    protected $table = 'point_finance_cash_advance';
    public $timestamps = false;

    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }

    public function scopeJoinEmployee($q)
    {
        $q->join('person', 'person.id', '=', 'point_finance_cash_advance.employee_id');
    }

    public function cashCashAdvance()
    {
        return $this->hasMany('\Point\PointFinance\Models\Cash\CashCashAdvance', 'cash_advance_id');
    }

    public function employee()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'employee_id');
    }

    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q)
    {
        $q->joinFormulir()
            ->notArchived()
            ->close()
            ->approvalApproved()
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentOrder($q, $cash_advance_edit)
    {
        $q->close()
            ->approvalApproved()
            ->orWhereIn('point_finance_cash_advance.id', $cash_advance_edit)
            ->orderByStandard();
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/finance/point/cash-advance/'.$id;
        }

        return '/finance/point/cash-advance/'.$id.'/archived';
    }

    public static function bladeEmail()
    {
        return 'point-finance::emails.finance.point.approval.cash-advance';
    }

    public function scopeHandedOver($query)
    {
        $query->where('handed_over', true);
    }
}
