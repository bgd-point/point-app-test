<?php 

namespace Point\PointFinance\Models\Cash;

use Point\Framework\Models\Formulir;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\ByTrait;

class CashCashAdvance extends Model
{
    protected $table = 'point_finance_cash_cash_advance';
    public $timestamps = false;

    use ByTrait;

    public function cash()
    {
        return $this->belongsTo('Point\PointFinance\Models\Cash\Cash', 'point_finance_cash_id');
    }

    public function cashAdvance()
    {
        return $this->belongsTo('Point\Finance\CashAdvance', 'cash_advance_id');
    }
}
