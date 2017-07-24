<?php

namespace Point\PointSales\Models\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ByTrait;

class PosPricing extends Model
{
    protected $table = 'point_sales_pos_pricing';
    public $timestamps = false;

    use ByTrait;

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

    public function scopeJoinDependencies($q)
    {
        $q->joinFormulir()->notArchived()->selectOriginal()->orderBy('id', 'desc');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_sales_pos_pricing.formulir_id');
    }

    public function scopeSelectOriginal($q)
    {
        $q->select(['point_sales_pos_pricing.*']);
    }

    public function scopeOrderByDate($q)
    {
        $q->orderBy(DB::raw('formulir.form_date', 'desc'));
    }

    public function scopeOrderByStandard($q)
    {
        $q->orderBy(DB::raw('formulir.id'), 'desc')
            ->orderBy(DB::raw('formulir.form_number', 'desc'));
    }

    public function scopeNotCancelled($q)
    {
        $q->where(DB::raw('formulir.form_status'), '<>', -1);
    }

    public function scopeNotArchived($q, $form_number = 0)
    {
        $q->whereNotNull('formulir.form_number');
        if ($form_number) {
            $q->where('formulir.form_number', '=', $form_number);
        }
    }

    public function scopeArchived($q, $form_number = 0)
    {
        $q->whereNull('formulir.form_number');
        if ($form_number) {
            $q->where('formulir.archived', '=', $form_number);
        }
    }

    public function formulir()
    {
        return $this->belongsTo('Point\Framework\Models\Formulir', 'formulir_id', 'id');
    }

    public function items()
    {
        return $this->hasMany('Point\PointSales\Models\Pos\PosPricingItem', 'pos_pricing_id');
    }

    public static function showUrl()
    {

        if ($this->formulir->form_number) {
            return '/sales/point/pos/pricing/'.$this->id;
        }
        return '/sales/point/pos/pricing/'.$this->id.'/archived';
    }
}
