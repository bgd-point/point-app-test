<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class ExpeditionOrderReference extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_order_reference';

    use ByTrait;
    use FormulirTrait;

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', $this->table . '.expedition_reference_id');
    }

    public function scopeJoinPerson($q)
    {
        $q->join('person', 'person.id', '=', 'point_expedition_order_reference.person_id');
    }

    public function getLinkReference()
    {
        $link = url("purchasing/point/purchase-order/" . $this->formulir->formulirable_id);
        if ($this->getReferenceType() == "Point\PointSales\Models\Sales\SalesOrder") {
            $link = url("sales/point/indirect/sales-order/" . $this->formulir->formulirable_id);
        }

        return $link;
    }

    public function getReferenceType()
    {
        return $this->formulir->formulirable_type;
    }

    public function formulir()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'expedition_reference_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointExpedition\Models\ExpeditionOrderReferenceItem',
            'point_expedition_order_reference_id');
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public function checkingDownpaymentReference()
    {
        // Check downpayment if its form sales and status cash
        if ($this->is_cash == 1) {
            $reference = $this->getReferenceType()::find($this->formulir->formulirable_id);
            return $reference->getTotalRemainingDownpayment($this->formulir->formulirable_id);
        }
        return true;
    }
}
