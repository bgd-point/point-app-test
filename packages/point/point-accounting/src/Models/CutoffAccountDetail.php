<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;

class CutOffAccountDetail extends Model
{
    protected $table = 'point_accounting_cut_off_account_detail';
    public $timestamps = false;

    public function coa()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Coa', 'coa_id');
    }
}
