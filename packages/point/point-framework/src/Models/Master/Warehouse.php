<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\MasterTrait;

class Warehouse extends Model
{
    protected $table = 'warehouse';

    use HistoryTrait, ByTrait, MasterTrait;

    /**
     * @param $q
     * @param $search
     *
     * @return mixed
     */
    public function scopeSearch($q, $disabled, $search)
    {
        return $q->where('disabled', $disabled ? : 0)
                ->where(function($query) use ($disabled, $search) {
                    $query->where('disabled', $disabled ? : 0);
                    if ($search) {
                        $query->where('name', 'like', '%'.$search.'%');
                    }
                });
    }

    /**
     * @return string
     */
    public function getCodeNameAttribute()
    {
        return '[' . $this->attributes['code'] . '] ' . $this->attributes['name'];
    }

    public function pettyCash()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'petty_cash_account');
    }
}
