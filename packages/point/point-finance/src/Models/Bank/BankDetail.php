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

    public function scopeJoinBank($q)
    {
        $q->join('point_finance_bank', 'point_finance_bank.id', '=', $this->table.'.point_finance_bank_id')
            ->join('formulir', 'formulir.id', '=', 'point_finance_bank.formulir_id');
    }

    public function scopeOrderByStandard($q)
    {
        $q->orderBy(\DB::raw('CAST(form_date as date)'), 'desc')
            ->orderBy('form_raw_number', 'desc');
    }

    public function scopeSelectOriginal($q)
    {
        $q->select([$this->table.'.*']);
    }

    public function scopeNotCanceled($q)
    {
        $q->where('formulir.form_status', '!=', -1);
    }

    public function scopeNotArchived($q, $form_number = 0)
    {
        $q->whereNotNull('formulir.form_number');
        if ($form_number) {
            $q->where('formulir.form_number', '=', $form_number);
        }
    }

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
