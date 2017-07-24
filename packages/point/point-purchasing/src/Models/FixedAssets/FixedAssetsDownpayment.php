<?php

namespace Point\PointPurchasing\Models\FixedAssets;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\FixedAssets\FixedAssetsDownpaymentVesa;

class FixedAssetsDownpayment extends Model
{
    use ByTrait, FormulirTrait, FixedAssetsDownpaymentVesa;

    protected $table = 'point_purchasing_fixed_assets_downpayment';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_fixed_assets_downpayment.supplier_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo('Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder', 'fixed_assets_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q, $supplier_id)
    {
        $q->joinFormulir()
            ->joinSupplier()
            ->where('point_purchasing_fixed_assets_downpayment.supplier_id', $supplier_id)
            ->notArchived()
            ->close()
            ->approvalApproved()
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentOrder($q, $supplier_id, $downpayment_edit)
    {
        $q->where('formulir.form_status', '=', 0)
            ->where('formulir.approval_status', '=', 1)
            ->where('person.id', '=', $supplier_id)
            ->orWhereIn('point_purchasing_fixed_assets_downpayment.id', $downpayment_edit)
            ->orderBy(\DB::raw('CAST(form_date as date)'), 'desc')
            ->orderBy(\DB::raw('SUBSTRING_INDEX(form_number, "/", -2)'));
    }

    public static function showUrl($id)
    {
        $downpayment = FixedAssetsDownpayment::find($id);

        if ($downpayment->formulir->form_number) {
            return '/purchasing/point/downpayment/'.$id;
        } else {
            return '/purchasing/point/downpayment/'.$id.'/archived';
        }
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.fixed-assets.downpayment';
    }
}
