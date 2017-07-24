<?php 

namespace Point\PointFinance\Models\Cash;

use Point\Framework\Models\Formulir;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class CashDetail extends Model
{
    protected $table = 'point_finance_cash_detail';
    public $timestamps = false;

    use ByTrait;

    public function cash()
    {
        return $this->belongsTo('Point\PointFinance\Models\Cash\Cash', 'point_finance_cash_id');
    }

    public function allocation()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Allocation', 'allocation_id');
    }

    public function coa()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function reference()
    {
        return $this->belongsTo('Point\Framework\Models\Formulir', 'form_reference_id');
    }
}
