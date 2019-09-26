<?php

namespace Point\PointManufacture\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class OutputProcess extends Model
{
    use ByTrait, FormulirTrait;

    protected $table = 'point_manufacture_output';
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

    public function scopeUniqueNumber($q)
    {
        $q->groupBy('formulir_id')->distinct();
    }

    public function scopeSearch($q, $date_from, $date_to, $search)
    {
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


    public function scopeForm($q, $formulir_id)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_manufacture_output.formulir_id')
            ->select(['*', \DB::raw('point_manufacture_output.*')])
            ->where('formulir_id', '=', $formulir_id);
    }

    public function scopeJoinInput($q)
    {
        $q->join('point_manufacture_input', 'point_manufacture_input.id', '=', 'point_manufacture_output.input_id');
    }

    public function scopeGetStepOne($q)
    {
        $q->join('formulir', 'formulir.id', '=',
            'point_manufacture_output.formulir_id')->join('point_manufacture_input',
            'point_manufacture_input.formulir_id', '=', 'point_manufacture_output.formulir_input_id')
            ->select(['*', \DB::raw('point_manufacture_output.*')])
            ->whereNull('archived')
            ->where('form_status', '=', 0)
            ->where('approval_status', '=', 1);
    }


    public static function material($formulir_id)
    {
        return InputMaterial::where('formulir_id', $formulir_id)->get();
    }

    /*public function details()
    {
        return $this->hasMany('\Point\PointManufacture\Models\outputDetail','formulir_output_id','formulir_id');
    }*/

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'product_id');
    }

    public function machine()
    {
        return $this->belongsTo('\Point\PointManufacture\Models\Machine', 'machine_id');
    }

    public function product()
    {
        return $this->hasMany('Point\PointManufacture\Models\OutputProduct', 'output_id');
    }

    public function input()
    {
        return $this->belongsTo('\Point\PointManufacture\Models\InputProcess', 'input_id');
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/manufacture/point/process-io/'.$class->input->process_id.'/output/'.$class->id;
        } else {
            return '/manufacture/point/process-io/'.$class->input->process_id.'/output/'.$class->id.'/archived';
        }
    }
}
