<?php

namespace Point\PointSales\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointSales\Vesa\ServiceDownpaymentVesa;

class Downpayment extends Model
{
    use ByTrait, FormulirTrait, ServiceDownpaymentVesa;

    protected $table = 'point_sales_service_downpayment';
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
        $q->join('person', 'person.id', '=', 'point_sales_service_downpayment.person_id');
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
        $q->where('formulir.form_status', '=', 0)
            ->where('formulir.approval_status', '=', 1)
            ->where('person.id', '=', $person_id)
            ->orWhereIn('point_sales_service_downpayment.id', $downpayment_edit)
            ->orderByStandard();
    }

    public function scopePaymentFinished($q, $sales_order_id)
    {
        $q->where('point_sales_service_downpayment.sales_order_id', '=', $sales_order_id)
            ->close()
            ->approvalApproved()
            ->whereNotNull('formulir.form_number');
    }

    public static function showUrl($id)
    {
        $downpayment = Downpayment::find($id);

        if ($downpayment->formulir->form_number) {
            return '/sales/point/service/downpayment/'.$downpayment->id;
        } else {
            return '/sales/point/service/downpayment/'.$downpayment->id.'/archived';
        }
    }
}
