<?php 

namespace Point\PointFinance\Models\Cheque;

use Illuminate\Database\Eloquent\Model;
use Point\PointFinance\Models\Cheque\ChequeDetail;

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

    public function statusLabel()
    {
        $label = '<i class="btn-xs btn-primary">done</i>';
        if ($this->status == -1) {
            $label = '<i class="btn-xs btn-danger">rejected</i>';
        } elseif ($this->status == 0) {
            $label = '<i class="btn-xs btn-warning">pending</i>';
        }

        return $label;
    }

    public static function searchList($status)
    {
        $list_check = ChequeDetail::joinCheque()->joinFormulir()->where('formulir.form_status', 1)->whereNull('formulir.archived')->whereIn('point_finance_cheque_detail.status', [-1, 0, 1])->select('point_finance_cheque_detail.*');
        if ($status != 'all') {
            $list_check = $list_check->where('point_finance_cheque_detail.status', $status);
        }

        return $list_check;
    }
}
