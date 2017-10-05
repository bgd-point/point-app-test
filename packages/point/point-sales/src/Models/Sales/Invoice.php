<?php

namespace Point\PointSales\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Traits\FormulirTrait;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\PaymentCollection;
use Point\PointSales\Vesa\InvoiceVesa;

class Invoice extends Model
{
    use ByTrait, FormulirTrait, InvoiceVesa;

    protected $table = 'point_sales_invoice';
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
        $q->join('person', 'person.id', '=', 'point_sales_invoice.person_id');
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
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentCollection($q, $person_id, $invoice_edit)
    {
        $q->open()
            ->approvalApproved()
            ->where('person.id', '=', $person_id)
            ->orWhereIn('point_sales_invoice.id', $invoice_edit)
            ->orderByStandard();
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointSales\Models\Sales\InvoiceItem', 'point_sales_invoice_id');
    }

    public static function showUrl($id)
    {
        $class = self::find($id);
        
        if ($class->formulir->form_number) {
            return '/sales/point/indirect/invoice/'.$class->id;
        } else {
            return '/sales/point/indirect/invoice/'.$class->id.'/archived';
        }
    }
}
