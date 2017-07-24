<?php

namespace Point\PointManufacture\Models;

use Illuminate\Database\Eloquent\Model;

class InputMaterial extends Model
{
    protected $table = 'point_manufacture_input_material';
    public $timestamps = false;

    public static function unit($item)
    {
        $item_unit = \Point\Framework\Models\Master\ItemUnit::where('item_id', '=', $item)->first();

        return $item_unit->converter;
    }

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'material_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public function input()
    {
        return $this->belongsTo('Point\PointManufacture\Models\InputProcess', 'input_id');
    }
}
