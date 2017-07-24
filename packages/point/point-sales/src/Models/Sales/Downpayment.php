<?php

namespace Point\PointSales\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointSales\Vesa\DownpaymentVesa;

class Downpayment extends Model
{
    use ByTrait, FormulirTrait, DownpaymentVesa;

    protected $table = 'point_sales_downpayment';
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

    public function scopeJoinPerson($q)
    {
        $q->join('person', 'person.id', '=', 'point_sales_downpayment.person_id');
    }

    public function salesOrder()
    {
        return $this->belongsTo('Point\PointSales\Models\Sales\SalesOrder', 'sales_order_id');
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public function scopeAvailableToCreatePaymentCollection($q, $person_id)
    {
        $q->joinFormulir()
            ->joinPerson()
            ->notArchived()
            ->approvalApproved()
            ->close()
            ->where('person.id', '=', $person_id)
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentCollection($q, $person_id, $downpayment_edit)
    {
        $q->open()
            ->approvalApproved()
            ->where('person.id', '=', $person_id)
            ->orWhereIn('point_sales_downpayment.id', $downpayment_edit)
            ->orderByStandard();
    }

    public function scopePaymentFinished($q, $sales_order_id)
    {
        $q->where('point_sales_downpayment.sales_order_id', '=', $sales_order_id)
            ->close()
            ->approvalApproved()
            ->whereNotNull('formulir.form_number');
    }

    public static function showUrl()
    {
        if ($this->formulir->form_number) {
            return '/sales/point/indirect/downpayment/'.$this->id;
        } else {
            return '/sales/point/indirect/downpayment/'.$this->id.'/archived';
        }
    }

    public static function bladeEmail()
    {
        return 'point-sales::app.emails.sales.point.approval.downpayment';
    }
}
