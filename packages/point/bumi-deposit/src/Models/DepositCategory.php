<?php

namespace Point\BumiDeposit\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;

class DepositCategory extends Model
{
    protected $table = 'bumi_deposit_category';
    public $timestamps = false;

    use ByTrait, HistoryTrait, MasterTrait;

    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%'. $search .'%');
    }
}
