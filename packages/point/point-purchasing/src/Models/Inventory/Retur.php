<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\Inventory\ReturVesa;

class Retur extends Model
{
    use ByTrait, FormulirTrait, ReturVesa;

    protected $table = 'point_purchasing_retur';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_retur.supplier_id');
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
        $q->open()
            ->approvalApproved()
            ->where('person.id', '=', $supplier_id)
            ->orWhereIn('point_purchasing_retur.id', $retur_edit)
            ->orderByStandard();
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Inventory\ReturItem', 'point_purchasing_retur_id');
    }

    public static function showUrl($id)
    {
        if ($this->formulir->form_number) {
            return '/purchasing/point/retur/'.$this->id;
        } else {
            return '/purchasing/point/retur/'.$this->id.'/archived';
        }
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.purchase-requisition';
    }
}
