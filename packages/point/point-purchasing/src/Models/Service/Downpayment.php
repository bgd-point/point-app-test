<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\Service\ServiceDownpaymentVesa;

class Downpayment extends Model
{
    use ByTrait, FormulirTrait, ServiceDownpaymentVesa;

    protected $table = 'point_purchasing_service_downpayment';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_service_downpayment.supplier_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo('Point\PointPurchasing\Models\Service\PurchaseOrder', 'purchasing_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q, $supplier_id)
    {
        $q->joinFormulir()
            ->joinSupplier()
            ->where('point_purchasing_service_downpayment.supplier_id', $supplier_id)
            ->notArchived()
            ->close()
            ->approvalApproved()
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentOrder($q, $supplier_id, $downpayment_edit)
    {
        $q->open()
            ->approvalApproved()
            ->where('person.id', '=', $supplier_id)
            ->orWhereIn('point_purchasing_service_downpayment.id', $downpayment_edit)
            ->orderByStandard();
    }

    public static function showUrl()
    {
        if ($this->formulir->form_number) {
            return '/purchasing/point/service/downpayment/'.$id;
        }

        return '/purchasing/point/service/downpayment/'.$id.'/archived';
    }
}
