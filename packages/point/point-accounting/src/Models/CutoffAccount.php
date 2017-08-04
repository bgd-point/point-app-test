<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointAccounting\Vesa\CutOffAccountVesa;

class CutOffAccount extends Model
{
    protected $table = 'point_accounting_cut_off_account';
    public $timestamps = false;

    use FormulirTrait, CutOffAccountVesa;

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

    public function cutOffAccountDetail()
    {
        return $this->hasMany('Point\PointAccounting\Models\CutOffAccountDetail','cut_off_account_id', 'id');
    }

    public static function bladeEmail()
    {
        return 'point-accounting::emails.accounting.point.approval.cut-off';
    }

    public static function showUrl($id)
    {
        $class = self::find($id);
        if ($class->formulir->form_number) {
            return '/accounting/point/cut-off/account/' . $class->id;
        }
        return '/accounting/point/cut-off/account/' . $class->id . '/archived';
    }
}
