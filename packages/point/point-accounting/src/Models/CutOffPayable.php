<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointAccounting\Vesa\CutOffPayableVesa;

class CutOffPayable extends Model
{
    protected $table = 'point_accounting_cut_off_payable';
    public $timestamps = false;

    use FormulirTrait, CutOffPayableVesa;

    /**
     * Inject function when saving
     *
     * @param array $options
     *
     * @return bool|null
     */
    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }

    public function cutOffPayableDetail()
    {
        return $this->hasMany('Point\PointAccounting\Models\CutOffPayableDetail', 'cut_off_payable_id', 'id');
    }

    public static function getSubledgerAmount($form_date, $coa_id)
    {
        $cut_off_payable = self::joinFormulir()
            ->approvalApproved()
            ->notArchived()
            // ->open()
            ->where('form_date', 'like', date('Y-m-d', strtotime($form_date)) . '%')
            ->selectOriginal()
            ->orderBy('formulir.id', 'desc')
            ->first();

        if (! $cut_off_payable) {
            return 0;
        }

        return $cut_off_payable->cutOffPayableDetail
            ->where('coa_id', $coa_id)
            ->sum('amount');
    }

    public static function bladeEmail()
    {
        return 'point-accounting::emails.accounting.point.approval.cut-off-payable';
    }
}
