<?php

namespace Point\PointSales\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Traits\FormulirTrait;

class Invoice extends Model
{
    use ByTrait, FormulirTrait;

    protected $table = 'point_sales_service_invoice';
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
        $q->join('person', 'person.id', '=', 'point_sales_service_invoice.person_id');
    }

    public function scopeJoinDetailService($q)
    {
        $q->join('point_sales_service_invoice_service', 'point_sales_service_invoice_service.point_sales_service_invoice_id', '=', 'point_sales_service_invoice.id');
    }

    public function scopeJoinService($q)
    {
        $q->join('service', 'service.id', '=', 'point_sales_service_invoice_service.service_id');
    }

    public function scopeAvailableToPaymentCollection($q)
    {
        $q->open()
            ->approvalApproved()
            ->notArchived()
            ->orderByStandard();
    }

    public function scopeAvailableToCreatePaymentCollection($q, $person_id)
    {
        $q->open()
            ->approvalApproved()
            ->where('person.id', '=', $person_id)
            ->notArchived()
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentCollection($q, $person_id, $invoice_edit)
    {
        $q->open()
            ->approvalApproved()
            ->where('person.id', '=', $person_id)
            ->orWhereIn('point_sales_service_invoice.id', $invoice_edit)
            ->orderByStandard();
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointSales\Models\Service\InvoiceItem', 'point_sales_service_invoice_id');
    }

    public function services()
    {
        return $this->hasMany('\Point\PointSales\Models\Service\InvoiceService', 'point_sales_service_invoice_id');
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/sales/point/service/invoice/'.$class->id;
        } else {
            return '/sales/point/service/invoice/'.$class->id.'/archived';
        }
    }
}
