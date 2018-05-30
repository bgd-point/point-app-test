<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Vesa\Inventory\DownpaymentVesa;

class Downpayment extends Model
{
    use ByTrait, FormulirTrait, DownpaymentVesa;

    protected $table = 'point_purchasing_downpayment';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_downpayment.supplier_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo('Point\PointPurchasing\Models\Inventory\PurchaseOrder', 'purchasing_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q, $supplier_id)
    {
        $q->joinFormulir()
            ->joinSupplier()
            ->where('point_purchasing_downpayment.supplier_id', $supplier_id)
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
            ->orWhereIn('point_purchasing_downpayment.id', $downpayment_edit)
            ->orderByStandard();
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/purchasing/point/downpayment/'.$class->id;
        } else {
            return '/purchasing/point/downpayment/'.$class->id.'/archived';
        }
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.downpayment';
    }
}
