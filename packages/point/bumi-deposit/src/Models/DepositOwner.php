<?php

namespace Point\BumiDeposit\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;

class DepositOwner extends Model
{
    protected $table = 'bumi_deposit_owner';
    public $timestamps = false;

    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%'. $search .'%');
    }

    use ByTrait, HistoryTrait, MasterTrait;
}
