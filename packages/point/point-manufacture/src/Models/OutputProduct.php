<?php

namespace Point\PointManufacture\Models;

use Illuminate\Database\Eloquent\Model;

class OutputProduct extends Model
{
    protected $table = 'point_manufacture_output_product';
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

    public function output()
    {
        return $this->belongsTo('Point\PointManufacture\Models\outputProcess', 'output_id');
    }
}
