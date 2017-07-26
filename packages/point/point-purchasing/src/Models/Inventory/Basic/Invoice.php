<?php

namespace Point\PointPurchasing\Models\Inventory\Basic;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Models\Inventory\Basic\Invoice;
use Point\PointPurchasing\Models\Inventory\Basic\PaymentOrder;

class Invoice extends Model
{
    use ByTrait, FormulirTrait;

    protected $table = 'point_purchasing_basic_invoice';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_basic_invoice.supplier_id');
    }

    public function scopeAvailableToPaymentOrder($q)
    {
        $invoice_locked = Invoice::getLockedInvoice();

        $q->open()
            ->approvalApproved()
            ->whereNotIn('point_purchasing_basic_invoice.formulir_id', $invoice_locked)
            ->notArchived()
            ->orderByStandard()
            ->groupBy('point_purchasing_basic_invoice.supplier_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q, $supplier_id)
    {
        $invoice_locked = Invoice::getLockedInvoice();
        
        $q->joinFormulir()
            ->joinSupplier()
            ->whereNotIn('point_purchasing_basic_invoice.formulir_id', $invoice_locked)
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('person.id', '=', $supplier_id)
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentOrder($q, $supplier_id, $invoice_edit)
    {
        $q->open()
            ->approvalApproved()
            ->where('person.id', '=', $supplier_id)
            ->orWhereIn('point_purchasing_basic_invoice.id', $invoice_edit)
            ->orderByStandard();
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Inventory\Basic\InvoiceItem', 'point_purchasing_basic_invoice_id');
    }

    public function getListSupplier()
    {
        $list_invoice_by_supplier = $this->joinFormulir()
            ->joinSupplier()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('supplier_id', '=', $this->supplier_id)
            ->selectOriginal()
            ->orderByStandard()
            ->get();


        foreach ($list_invoice_by_supplier as $invoice_by_supplier) {
            echo date_Format_view($invoice_by_supplier->formulir->form_date);
            echo "<a href='".url('purchasing/point/invoice/' . $this->id)."'> ".$invoice_by_supplier->formulir->form_number."</a>";
            echo "<br/>";
        }
    }

    public static function showUrl()
    {
        if ($this->formulir->form_number) {
            return '/purchasing/point/invoice/basic/'.$id.'/show';
        }

        return '/purchasing/point/invoice/basic/'.$id.'/archived';
    }

    public static function getLockedInvoice() {
        $locking_id = PaymentOrder::joinFormulir()->whereIn('form_status', [1, 0])->select('formulir_id')->get();
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
