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
        $invoice_locked = Invoice::getLockedInvoice();

        $q->open()
            ->approvalApproved()
            ->whereNotIn('point_sales_service_invoice.formulir_id', $invoice_locked)
            ->notArchived()
            ->orderByStandard();
    }

    public function scopeAvailableToCreatePaymentCollection($q, $person_id)
    {
        $invoice_locked = Invoice::getLockedInvoice();
        $q->open()
            ->approvalApproved()
            ->whereNotIn('point_sales_service_invoice.formulir_id', $invoice_locked)
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

    public static function showUrl()
    {
        if ($this->formulir->form_number) {
            return '/sales/point/service/invoice/'.$this->id;
        } else {
            return '/sales/point/service/invoice/'.$this->id.'/archived';
        }
    }

    /**
     * @param $locking_id
     *
     * @return array
     */
    public static function getLockedInvoice() {
        $locking_id = PaymentCollection::joinFormulir()->whereIn('form_status', [1, 0])->select('formulir_id')->get();
        if($locking_id->count()) {
            $locking_id = array_flatten(array_values($locking_id->toArray()));
        }

        return FormulirLock::join('formulir', 'formulir.id', '=','formulir_lock.locked_id')
            ->whereIn('locking_id', $locking_id)
            ->where('locked', true)
            ->where('formulir.formulirable_type', '=', get_class(new Invoice()))
            ->select('formulir.id')
            ->get()
            ->toArray();
    }
}
