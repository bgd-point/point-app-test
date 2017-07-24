<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Traits\FormulirTrait;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointExpedition\Vesa\ExpeditionOrderVesa;

class ExpeditionOrder extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_order';

    use ByTrait, FormulirTrait, ExpeditionOrderVesa;

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
        $q->join('person', 'person.id', '=', 'point_expedition_order.expedition_id');
    }

    public function scopeAvailableToInvoiceGroupExpedition($q)
    {
        $q->open()
            ->approvalApproved()
            ->orderByStandard()
            ->groupBy('point_expedition_order.expedition_id');
    }

    public function scopeAvailableToInvoice($q, $expedition_id)
    {
        $q->open()
            ->approvalApproved()
            ->where('point_expedition_order.expedition_id', '=', $expedition_id)
            ->orderByStandard();
    }

    public function items()
    {
        return $this->hasMany('\Point\PointExpedition\Models\ExpeditionOrderItem', 'point_expedition_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function expedition()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'expedition_id');
    }

    public function showUrl()
    {
        if ($this->formulir->form_number) {
            return '/expedition/point/expedition-order/' . $this->id;
        }

        return '/expedition/point/expedition-order/' . $this->id . '/archived';
    }

    public function person()
    {
        return $this->morphTo();
    }

    public function getListExpeditionOrder()
    {
        $list_invoice_order_single = $this->joinFormulir()
            ->availableToInvoice($this->expedition_id)
            ->selectOriginal()
            ->get();
            
        foreach ($list_invoice_order_single as $invoice_order_single) {
            echo "<li><a href='".url('expedition/point/expedition-order/'.$invoice_order_single->id)."'>".$invoice_order_single->formulir->form_number."</a>
                <br>Total Fee ".number_format_quantity($invoice_order_single->total)."</li>";
        }
    }

    public static function getExpeditionReferenceIsOpen()
    {
        $list_expedition_reference = ExpeditionOrderReference::where('finish', false)->get();
        $array_expedition_reference_id_open = [];
        foreach ($list_expedition_reference as $expedition_reference) {
            if ($expedition_reference->is_cash) {
                if ($expedition_reference->checkingDownpaymentReference() < 1) {
                    continue;
                }
            }
            
            if ($expedition_reference->expedition_order_id == null) {
                array_push($array_expedition_reference_id_open, $expedition_reference->expedition_reference_id);
            }
            
            foreach ($expedition_reference->items as $expedition_reference_item) {
                $quantity_remaining = ReferHelper::remaining(get_class($expedition_reference_item), $expedition_reference_item->id, $expedition_reference_item->quantity);
                if (in_array($expedition_reference->expedition_reference_id, $array_expedition_reference_id_open)) {
                    continue;
                }
                
                if ($quantity_remaining != 0) {
                    array_push($array_expedition_reference_id_open, $expedition_reference->expedition_reference_id);
                }
            }
            
            if ($expedition_reference->expedition_order_id == null) {
                array_push($array_expedition_reference_id_open, $expedition_reference->expedition_reference_id);
            }
        }

        return $array_expedition_reference_id_open;
    }

    public static function bladeEmail()
    {
        return 'point-expedition::emails.expedition.point.approval.expedition-order';
    }

    public function reference()
    {
        return FormulirHelper::getLockedModel($this->formulir_id);
    }
}
