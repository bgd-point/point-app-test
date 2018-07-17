<?php

namespace Point\PointSales\Models\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class PosRetur extends Model
{
    use ByTrait, FormulirTrait;

    protected $table = 'point_sales_pos_retur';
    public $timestamps = false;

    public function items()
    {
        return $this->hasMany('Point\PointSales\Models\Pos\PosReturItem', 'pos_retur_id');
    }

    public function customer()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'customer_id');
    }

    public function pos()
    {
        return $this->belongsTo('\Point\PointSales\Models\Pos\Pos', 'pos_id');
    }
}
