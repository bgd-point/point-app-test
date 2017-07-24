<?php 

namespace Point\PointFinance\Models\Bank;

use Point\Framework\Models\Formulir;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class BankDetail extends Model
{
    protected $table = 'point_finance_bank_detail';
    public $timestamps = false;

    use ByTrait;

    public function bank()
    {
        return $this->belongsTo('Point\PointFinance\Models\Bank\Bank', 'point_finance_bank_id');
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
