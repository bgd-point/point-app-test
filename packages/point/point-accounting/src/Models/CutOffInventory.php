<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointAccounting\Vesa\CutOffInventoryVesa;

class CutOffInventory extends Model
{
    protected $table = 'point_accounting_cut_off_inventory';
    public $timestamps = false;

    use FormulirTrait, CutOffInventoryVesa;

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

    public function cutOffInventoryDetail()
    {
        return $this->hasMany('Point\PointAccounting\Models\CutOffInventoryDetail', 'cut_off_inventory_id');
    }

    public static function getSubledgerAmount($form_date, $coa_id)
    {
        $cutoff_inventory = self::joinFormulir()
            ->approvalApproved()
            ->open()
            ->where('form_date', 'like', date('Y-m-d', strtotime($form_date)) . '%')
            ->notArchived()
            ->selectOriginal()
            ->orderBy('id', 'desc')
            ->first();

        dd($cutoff_inventory);

        if ($cutoff_inventory) {
            $cutoff_inventory = $cutoff_inventory->cutOffInventoryDetail
                ->where('coa_id', $coa_id)
                ->sum('amount');
        }

        return $cutoff_inventory ? : 0;
    }

    public static function bladeEmail()
    {
        return 'point-accounting::emails.accounting.point.approval.cut-off-inventory';
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/accounting/point/cut-off/inventory/' . $id;
        }

        return '/accounting/point/cut-off/inventory/' . $id . '/archived';
    }
}
