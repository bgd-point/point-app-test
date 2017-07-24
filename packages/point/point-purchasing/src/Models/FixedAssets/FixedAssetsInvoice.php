<?php

namespace Point\PointPurchasing\Models\FixedAssets;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;
use Point\PointPurchasing\Vesa\FixedAssets\FixedAssetsInvoiceVesa;

class FixedAssetsInvoice extends Model
{
    use ByTrait, FormulirTrait, FixedAssetsInvoiceVesa;

    protected $table = 'point_purchasing_fixed_assets_invoice';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_fixed_assets_invoice.supplier_id');
    }

    public function scopeJoinInvoiceDetail($q)
    {
        $q->join('point_purchasing_fixed_assets_invoice_detail', 'point_purchasing_fixed_assets_invoice_detail.fixed_assets_invoice_id', '=', $this->table.'.id');
    }

    public function scopeAvailableToPaymentOrder($q)
    {
        $q->open()
            ->approvalApproved()
            ->notArchived()
            ->orderByStandard()
            ->groupBy('point_purchasing_fixed_assets_invoice.supplier_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q, $supplier_id)
    {
        $q->joinFormulir()
            ->joinSupplier()
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
            ->orWhereIn('point_purchasing_fixed_assets_invoice.id', $invoice_edit)
            ->orderByStandard();
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function details()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoiceDetail', 'fixed_assets_invoice_id');
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
            echo "<a href='".url('purchasing/point/fixed-assets/invoice/' . $this->id)."'> ".$invoice_by_supplier->formulir->form_number."</a>";
            echo "<br/>";
        }
    }

    public static function getDetailInvoice($coa_id, $formulir_id)
    {
        $invoice = FixedAssetsInvoice::joinInvoiceDetail()
            ->where('formulir_id', $formulir_id)
            ->where('coa_id', $coa_id)
            ->select('point_purchasing_fixed_assets_invoice_detail.*')
            ->orderBy('id', 'desc')
            ->first();

        if (!$invoice) {
            return null;
        }

        return $invoice;
    }
}
