<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Models\Inventory\Invoice;
use Point\PointPurchasing\Models\Inventory\PaymentOrder;
use Point\PointPurchasing\Vesa\Inventory\InvoiceVesa;

class Invoice extends Model
{
    use ByTrait, FormulirTrait, InvoiceVesa;

    protected $table = 'point_purchasing_invoice';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_invoice.supplier_id');
    }

    public function scopeAvailableToPaymentOrder($q)
    {
        $q->open()
            ->approvalApproved()
            ->notArchived()
            ->orderByStandard()
            ->groupBy('point_purchasing_invoice.supplier_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q, $supplier_id)
    {
        // not relevant
        // $invoice_locked = Invoice::getLockedInvoice();

        $q->joinFormulir()
            ->joinSupplier()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('person.id', '=', $supplier_id)
            ->selectOriginal();
    }

    public function scopeAvailableToEditPaymentOrder($q, $supplier_id, $invoice_edit)
    {
        $q->open()
            ->approvalApproved()
            ->where('person.id', '=', $supplier_id)
            ->orWhereIn('point_purchasing_invoice.id', $invoice_edit)
            ->orderByStandard();
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Inventory\InvoiceItem', 'point_purchasing_invoice_id');
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

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/purchasing/point/invoice/'.$id;
        }

        return '/purchasing/point/invoice/'.$id.'/archived';
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
