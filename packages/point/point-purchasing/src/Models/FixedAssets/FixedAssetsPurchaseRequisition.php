<?php

namespace Point\PointPurchasing\Models\FixedAssets;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\FixedAssets\FixedAssetsPurchaseRequisitionVesa;

class FixedAssetsPurchaseRequisition extends Model
{
    use ByTrait, FormulirTrait, FixedAssetsPurchaseRequisitionVesa;

    protected $table = 'point_purchasing_fixed_assets_requisition';
    public $timestamps = false;

    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }
    
    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_fixed_assets_requisition.supplier_id');
    }
    
    public function scopeJoinEmployee($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_fixed_assets_requisition.employee_id');
    }

    public function lockedBy()
    {
        return $this->hasMany('\Point\Framework\Models\FormulirLock', 'locked_id', 'formulir_id');
    }

    public function details()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisitionDetail', 'fixed_assets_requisition_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function employee()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'employee_id');
    }

    public function accountAssets()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'account_asset_id');
    }

    public static function showUrl($id)
    {
        $purchase_requisition = PurchaseRequisition::find($id);

        if ($purchase_requisition->formulir->form_number) {
            return '/purchasing/point/purchase-requisition/'.$id;
        } else {
            return '/purchasing/point/purchase-requisition/'.$id.'/archived';
        }
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.fixed-assets.purchase-requisition';
    }
}
