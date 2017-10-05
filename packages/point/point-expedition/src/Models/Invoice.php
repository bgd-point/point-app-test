<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\FormulirTrait;
use Point\PointExpedition\Vesa\InvoiceVesa;

class Invoice extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_invoice';

    use ByTrait, FormulirTrait, InvoiceVesa;

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

    public function scopeJoinExpedition($q)
    {
        $q->join('person', 'person.id', '=', 'point_expedition_invoice.expedition_id');
    }

    public function scopeJoinExpeditionInvoiceItem($q)
    {
        $q->join('point_expedition_invoice_item', 'point_expedition_invoice_item.point_expedition_invoice_id', '=',
            'point_expedition_invoice.id');
    }

    public function scopeSelectItem($q)
    {
        $q->select(['point_expedition_invoice_item.*']);
    }

    public function scopeAvailableToPaymentOrder($q)
    {
        $q->joinFormulir()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->groupBy('point_expedition_invoice.expedition_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q, $expedition_id)
    {
        $q->joinFormulir()
            ->joinExpedition()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('person.id', '=', $expedition_id)
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentOrder($q, $expedition_id, $invoice_edit)
    {
        $q->joinFormulir()
            ->joinExpedition()
            ->open()
            ->approvalApproved()
            ->notArchived()
            ->where('person.id', '=', $expedition_id)
            ->orWhereIn('point_expedition_invoice.id', $invoice_edit)
            ->selectOriginal()
            ->orderByStandard();
    }

    public function expedition()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'expedition_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointExpedition\Models\InvoiceItem', 'point_expedition_invoice_id');
    }

    public function getLinkInvoice()
    {
        $url = url('expedition/point/invoice/' . $this->id);
        if ($this->type_of_tax == null && $this->type_of_fee == null) {
            $url = url('expedition/point/invoice/basic/' . $this->id);
        }

        return $url;
    }

    public function getListExpedition()
    {
        $list_invoice_by_expedition = $this->joinFormulir()
            ->joinExpedition()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('expedition_id', '=', $this->expedition_id)
            ->selectOriginal()
            ->orderByStandard()
            ->get();


        foreach ($list_invoice_by_expedition as $invoice_by_expedition) {
            echo date_Format_view($invoice_by_expedition->formulir->form_date);
            echo "<a href='".$invoice_by_expedition->getLinkInvoice()."'> ".$invoice_by_expedition->formulir->form_number."</a>";
            echo "<br/>";
        }
    }

    public function getExpeditionOrder()
    {
        return FormulirHelper::getLockedModel($this->formulir_id);

    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/expedition/point/invoice/' . $id;
        }

        return '/expedition/point/invoice/' . $id . '/archived';
    }
}
