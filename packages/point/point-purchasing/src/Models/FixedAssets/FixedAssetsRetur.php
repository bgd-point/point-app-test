<?php

namespace Point\PointPurchasing\Models\FixedAssets;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\ReturVesaFixedAssets;

class FixedAssetsRetur extends Model
{
    use ByTrait, FormulirTrait;

    protected $table = 'point_purchasing_fixed_assets_retur';
    public $timestamps = false;

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

    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_fixed_assets_retur.supplier_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q, $supplier_id)
    {
        $q->joinFormulir()
            ->joinSupplier()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('person.id', '=', $supplier_id)
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentOrder($q, $supplier_id, $retur_edit)
    {
        $q->where('formulir.form_status', '=', 0)
            ->where('formulir.approval_status', '=', 1)
            ->where('person.id', '=', $supplier_id)
            ->orWhereIn('point_purchasing_fixed_assets_retur.id', $retur_edit)
            ->orderBy(\DB::raw('CAST(form_date as date)'), 'desc')
            ->orderBy(\DB::raw('SUBSTRING_INDEX(form_number, "/", -2)'));
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\FixedAssets\ReturItem', 'point_purchasing_fixed_assets_retur_id');
    }
}
