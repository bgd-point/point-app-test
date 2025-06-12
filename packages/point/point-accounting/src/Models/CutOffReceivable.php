<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointAccounting\Vesa\CutOffReceivableVesa;

class CutOffReceivable extends Model
{
    protected $table = 'point_accounting_cut_off_receivable';
    public $timestamps = false;

    use FormulirTrait, CutOffReceivableVesa;

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

    public function cutOffReceivableDetail()
    {
        return $this->hasMany('Point\PointAccounting\Models\CutOffReceivableDetail', 'cut_off_receivable_id', 'id');
    }

    public static function getSubledgerAmount($form_date, $coa_id)
    {
        $cut_off_receivable = self::joinFormulir()
            ->approvalApproved()
            ->notArchived()
            // ->open()
            ->where('form_date', 'like', date('Y-m-d', strtotime($form_date)) . '%')
            ->selectOriginal()
            ->orderBy('formulir.id', 'desc')
            ->first();

        if (! $cut_off_receivable) {
            return 0;
        }

        return $cut_off_receivable->cutOffReceivableDetail
            ->where('coa_id', $coa_id)
            ->sum('amount');
    }

    public static function bladeEmail()
    {
        return 'point-accounting::emails.accounting.point.approval.cut-off-receivable';
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/accounting/point/cut-off/receivable/' . $id;
        }

        return '/accounting/point/cut-off/receivable/' . $id . '/archived';
    }
}
