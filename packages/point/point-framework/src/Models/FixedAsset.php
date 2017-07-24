<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $table = 'fixed_asset';
    public $timestamps = false;

    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'account_id');
    }
}
