<?php

namespace Point\BumiDeposit\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;

class BankProduct extends Model
{
    protected $table = 'bumi_deposit_bank_product';
    public $timestamps = false;

    use ByTrait, HistoryTrait, MasterTrait;

    public function bank()
    {
        return $this->belongsTo('\Point\BumiDeposit\Models\DepositBank', 'bumi_deposit_bank_id');
    }
}
