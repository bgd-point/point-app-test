<?php

namespace Point\BumiDeposit\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;

class Bank extends Model
{
    protected $table = 'bumi_deposit_bank';
    public $timestamps = false;

    use ByTrait, HistoryTrait, MasterTrait;

    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%'. $search .'%')
            ->orWhere('branch', 'like', '%'.$search.'%')
            ->orWhere('address', 'like', '%'.$search.'%')
            ->orWhere('phone', 'like', '%'.$search.'%')
            ->orWhere('fax', 'like', '%'.$search.'%');
    }

    public function accounts()
    {
        return $this->hasMany('Point\BumiDeposit\Models\BankAccount', 'bumi_deposit_bank_id');
    }

    public function products()
    {
        return $this->hasMany('Point\BumiDeposit\Models\BankProduct', 'bumi_deposit_bank_id');
    }
}
