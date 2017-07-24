<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;

class CutOffReceivableDetail extends Model
{
    protected $table = 'point_accounting_cut_off_receivable_detail';
    public $timestamps = false;

    public function person()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Person','subledger_id');
    }

    public function coa()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Coa','coa_id');
    }

    public function cutoffReceivable()
    {
        return $this->belongsTo('Point\PointAccounting\Models\CutOffReceivable','cut_off_receivable_id');
    }

    public function scopeJoinReceivable($q)
    {
        $q->join('point_accounting_cut_off_receivable', 'point_accounting_cut_off_receivable.id', '=', $this->table.'.cut_off_receivable_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_accounting_cut_off_receivable.formulir_id');
    }

}
