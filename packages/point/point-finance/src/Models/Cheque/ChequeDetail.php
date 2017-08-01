<?php 

namespace Point\PointFinance\Models\Cheque;

use Illuminate\Database\Eloquent\Model;

class ChequeDetail extends Model
{
    protected $table = 'point_finance_cheque_detail';
    public $timestamps = false;

    public function cheque()
    {
        return $this->belongsTo('Point\PointFinance\Models\Cheque\Cheque', 'point_finance_cheque_id');
    }
    
    public function scopeJoinCheque($q)
    {
        $q->join('point_finance_cheque', 'point_finance_cheque.id', '=', $this->table.'.point_finance_cheque_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_finance_cheque.formulir_id');
    }
}
