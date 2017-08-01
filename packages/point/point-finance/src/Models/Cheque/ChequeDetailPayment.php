<?php 

namespace Point\PointFinance\Models\Cheque;

use Point\Framework\Models\Formulir;
use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class ChequeDetailPayment extends Model
{
    protected $table = 'point_finance_cheque_detail_payment';
    public $timestamps = false;

    use ByTrait;

    public function cheque()
    {
        return $this->belongsTo('Point\PointFinance\Models\Cheque\Cheque', 'point_finance_cheque_id');
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
