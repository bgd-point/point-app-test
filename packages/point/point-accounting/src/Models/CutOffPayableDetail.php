<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;

class CutOffPayableDetail extends Model
{
    protected $table = 'point_accounting_cut_off_payable_detail';
    public $timestamps = false;

    public function person()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Person', 'subledger_id');
    }

    public function coa()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function cutoffPayable()
    {
        return $this->belongsTo('Point\PointAccounting\Models\CutOffPayable', 'cut_off_payable_id');
    }

    public function scopeJoinPayable($q)
    {
        $q->join('point_accounting_cut_off_payable', 'point_accounting_cut_off_payable.id', '=', $this->table.'.cut_off_payable_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_accounting_cut_off_payable.formulir_id');
    }
}
