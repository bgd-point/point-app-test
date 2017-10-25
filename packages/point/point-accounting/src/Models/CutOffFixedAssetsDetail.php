<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;

class CutOffFixedAssetsDetail extends Model
{
    protected $table = 'point_accounting_cut_off_fixed_assets_detail';
    public $timestamps = false;

    public function suplier()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function coa()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function scopeJoinFixedAssets($q)
    {
        $q->join('point_accounting_cut_off_fixed_assets', 'point_accounting_cut_off_fixed_assets.id', '=', $this->table.'.fixed_assets_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_accounting_cut_off_fixed_assets.formulir_id');
    }

    public static function getTotalPrice($coa_id, $fixed_assets_id)
    {
        $amount = CutOffFixedAssetsDetail::where('coa_id', $coa_id)->where('fixed_assets_id', $fixed_assets_id)->sum('total_price');
        return $amount;
    }
}
