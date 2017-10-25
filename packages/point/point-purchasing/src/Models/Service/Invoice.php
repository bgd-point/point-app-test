<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Models\Service\Invoice;
use Point\PointPurchasing\Models\Service\PaymentOrder;

class Invoice extends Model
{
    use ByTrait, FormulirTrait;

    protected $table = 'point_purchasing_service_invoice';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_service_invoice.person_id');
    }

    public function scopeJoinDetailService($q)
    {
        $q->join('point_purchasing_service_invoice_service', 'point_purchasing_service_invoice_service.point_purchasing_service_invoice_id', '=', 'point_purchasing_service_invoice.id');
    }

    public function scopeJoinService($q)
    {
        $q->join('service', 'service.id', '=', 'point_purchasing_service_invoice_service.service_id');
    }


    public function scopeAvailableToPaymentOrder($q)
    {
        $invoice_locked = Invoice::getLockedInvoice();

        $q->open()
            ->whereNotIn('point_purchasing_service_invoice.formulir_id', $invoice_locked)
            ->approvalApproved()
            ->notArchived()
            ->orderByStandard();
    }

    public function scopeAvailableToCreatePaymentOrder($q, $person_id)
    {
        $invoice_locked = Invoice::getLockedInvoice();
        
        $q->open()
            ->whereNotIn('point_purchasing_service_invoice.formulir_id', $invoice_locked)
            ->approvalApproved()
            ->notArchived()
            ->where('person.id', '=', $person_id)
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentOrder($q, $person_id, $invoice_edit)
    {
        $q->open()
            ->approvalApproved()
            ->where('person.id', '=', $person_id)
            ->orWhereIn('point_purchasing_service_invoice.id', $invoice_edit)
            ->orderByStandard();
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Service\InvoiceItem', 'point_purchasing_service_invoice_id');
    }

    public function services()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Service\InvoiceService', 'point_purchasing_service_invoice_id');
    }

    public static function showUrl($id)
    {
        if ($this->formulir->form_number) {
            return '/purchasing/point/service/invoice/'.$this->id;
        } else {
            return '/purchasing/point/service/invoice/'.$this->id.'/archived';
        }
    }

    public static function getLockedInvoice()
    {
        $locking_id = PaymentOrder::joinFormulir()->whereIn('form_status', [1, 0])->select('formulir_id')->get();
        if ($locking_id->count()) {
            $locking_id = array_flatten(array_values($locking_id->toArray()));
        }

        return FormulirLock::join('formulir', 'formulir.id', '=', 'formulir_lock.locked_id')
            ->whereIn('locking_id', $locking_id)
            ->where('locked', true)
            ->where('formulir.formulirable_type', '=', get_class(new Invoice()))
            ->select('formulir.id')
            ->get()
            ->toArray();
    }
}
