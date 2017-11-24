<?php

namespace Point\PointManufacture\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointManufacture\Vesa\InputVesa;

class InputProcess extends Model
{
    use ByTrait, FormulirTrait, InputVesa;

    protected $table = 'point_manufacture_input';
    public $timestamps = false;

    /**
     * Inject function when saving
     *
     * @param array $options
     *
     * @return bool|null
     */
    public function scopeJoinMachine($q)
    {
        $q->join('point_manufacture_machine', 'point_manufacture_machine.id', '=', $this->table.'.machine_id');
    }

    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }

    public function scopeUniqueNumber($q)
    {
        $q->groupBy('formulir_id')->distinct();
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
                ->orWhere('code', 'like', '%' . $search . '%')
                ->orWhere('form_number', 'like', '%' . $search . '%');
        }
    }


    public function scopeGetInput($q, $process_id)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_manufacture_input.formulir_id')
            ->select(['*', \DB::raw('point_manufacture_input.*')])
            ->where('process_id', $process_id)
            ->whereNull('archived')
            ->where('form_status', '=', 0)
            ->where('approval_status', '=', 1);
    }

    public function scopeForm($q, $formulir_id)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_manufacture_input.formulir_id')->select([
            '*',
            \DB::raw('point_manufacture_input.*')
        ])->where('formulir_id', '=', $formulir_id);
    }


    public function process()
    {
        return $this->belongsTo('Point\PointManufacture\Models\Process', 'process_id');
    }

    public function product()
    {
        return $this->hasMany('Point\PointManufacture\Models\InputProduct', 'input_id');
    }

    public function material()
    {
        return $this->hasMany('Point\PointManufacture\Models\InputMaterial', 'input_id');
    }

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public function machine()
    {
        return $this->belongsTo('\Point\PointManufacture\Models\Machine', 'machine_id');
    }

    public function unit()
    {
        return $this->hasMany('\Point\Framework\Models\Master\ItemUnit', 'item_id');
    }

    public static function bladeEmail()
    {
        return 'point-manufacture::emails.manufacture.point.approval.input';
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/manufacture/point/process-io/'.$class->process_id.'/input/'.$class->id;
        } else {
            return '/manufacture/point/process-io/'.$class->process_id.'/input/'.$class->id.'/archived';
        }
    }
}
