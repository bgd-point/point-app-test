<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointExpedition\Vesa\DownpaymentVesa;

class Downpayment extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_downpayment';

    use ByTrait, FormulirTrait, DownpaymentVesa;

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

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/expedition/point/downpayment/' . $class->id;
        }

        return '/expedition/point/downpayment/' . $class->id . '/archived';
    }

    public function expedition()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'expedition_id');
    }

    public function scopeJoinExpedition($q)
    {
        $q->join('person', 'person.id', '=', 'point_expedition_downpayment.expedition_id');
    }

    public function scopeAvailableToCreatePaymentOrder($q, $expedition_id)
    {
        $q->joinFormulir()
            ->joinExpedition()
            ->where('point_expedition_downpayment.expedition_id', $expedition_id)
            ->notArchived()
            ->close()
            ->approvalApproved()
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeAvailableToEditPaymentOrder($q, $expedition_id, $downpayment_edit)
    {
        $q->joinFormulir()
            ->joinExpedition()
            ->where('person.id', '=', $expedition_id)
            ->close()
            ->approvalApproved()
            ->notArchived()
            ->orWhereIn('point_expedition_downpayment.id', $downpayment_edit)
            ->selectOriginal()
            ->orderByStandard();
    }

    public static function bladeEmail()
    {
        return 'point-expedition::emails.expedition.point.approval.downpayment';
    }
}
