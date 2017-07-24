<?php

namespace Point\PointManufacture\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class Formula extends Model
{
    use ByTrait, FormulirTrait;

    protected $table = 'point_manufacture_formula';
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

    public function scopeSearch($q, $order_by, $order_type, $status, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $q->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $q->orderBy($order_by, $order_type);
        } else {
            $q->orderByStandard();
        }

        if ($date_from) {
            $q->where('form_date', '>=', \DateHelper::formatDB($date_from, 'start'));
        }

        if ($date_to) {
            $q->where('form_date', '<=', \DateHelper::formatDB($date_to, 'end'));
        }

        if ($search) {
            $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('form_number', 'like', '%' . $search . '%');
        }
    }

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public function process()
    {
        return $this->belongsTo('Point\PointManufacture\Models\Process', 'process_id');
    }

    public function product()
    {
        return $this->hasMany('Point\PointManufacture\Models\FormulaProduct', 'formula_id');
    }

    public function material()
    {
        return $this->hasMany('Point\PointManufacture\Models\FormulaMaterial', 'formula_id');
    }

    public static function bladeEmail()
    {
        return 'point-manufacture::emails.manufacture.point.approval.formula';
    }
}
