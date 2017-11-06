<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\Inventory\CashAdvanceVesa;

class CashAdvance extends Model
{
    use ByTrait, FormulirTrait, CashAdvanceVesa;

    protected $table = 'point_purchasing_cash_advance';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_cash_advance.employee_id');
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo('Point\PointPurchasing\Models\Inventory\PurchaseRequisition', 'purchase_requisition_id');
    }

    public function employee()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'employee_id');
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
            ->orWhereIn('point_purchasing_cash_advance.id', $cash_advance_edit)
            ->orderByStandard();
    }

    public static function showUrl($id)
    {
        $class = self::find($id);
        if ($class->formulir->form_number) {
            return '/purchasing/point/cash-advance/'.$id;
        }

        return '/purchasing/point/cash-advance/'.$id.'/archived';
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.cash-advance';
    }
}
