<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointAccounting\Vesa\CutOffFixedAssetsVesa;

class CutOffFixedAssets extends Model
{

    protected $table = 'point_accounting_cut_off_fixed_assets';
    public $timestamps = false;

    use FormulirTrait, CutOffFixedAssetsVesa;

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

    public function cutOffFixedAssetsDetail()
    {
        return $this->hasMany('Point\PointAccounting\Models\CutOffFixedAssetsDetail','fixed_assets_id', 'id');
    }

    public static function getSubledgerAmount($form_date, $coa_id)
    {
        $cut_off_fixed_assets = self::joinFormulir()
            ->approvalApproved()
            ->notArchived()
            ->open()
            ->where('form_date', 'like', date('Y-m-d', strtotime($form_date)) . '%')
            ->selectOriginal()
            ->orderBy('formulir.id', 'desc')
            ->first();

        if ($cut_off_fixed_assets) {
            $cut_off_fixed_assets = $cut_off_fixed_assets->cutOffFixedAssetsDetail
                ->where('coa_id', $coa_id)
                ->sum('total_price');
        }

        return $cut_off_fixed_assets ? : 0;
    }

    public static function bladeEmail()
    {
        return 'point-accounting::emails.accounting.point.approval.cut-off-fixed-assets';
    }
}
