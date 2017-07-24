<?php

namespace Point\PointManufacture\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class FormulaProduct extends Model
{
    use ByTrait;

    protected $table = 'point_manufacture_formula_product';
    public $timestamps = false;

    public static function unit($item)
    {
        $item_unit = \Point\Framework\Models\Master\ItemUnit::where('item_id', '=', $item)->first();

        return $item_unit->converter;
    }

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public function formula()
    {
        return $this->belongsTo('Point\PointManufacture\Models\Formula', 'formula_id');
    }
}
