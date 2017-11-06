<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\Inventory\PurchaseRequisitionVesa;

class PurchaseRequisition extends Model
{
    use ByTrait, FormulirTrait, PurchaseRequisitionVesa;

    protected $table = 'point_purchasing_requisition';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_requisition.employee_id');
    }

    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_requisition.supplier_id');
    }

    public function lockedBy()
    {
        return $this->hasMany('\Point\Framework\Models\FormulirLock', 'locked_id', 'formulir_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Inventory\PurchaseRequisitionItem', 'point_purchasing_requisition_id');
    }

    public function employee()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'employee_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public static function showUrl($id)
    {
        $class = self::find($id);
        if ($class->formulir->form_number) {
            return '/purchasing/point/purchase-requisition/'.$id;
        } else {
            return '/purchasing/point/purchase-requisition/'.$id.'/archived';
        }
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.purchase-requisition';
    }
}
