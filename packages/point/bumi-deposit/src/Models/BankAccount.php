<?php

namespace Point\BumiDeposit\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;

class BankAccount extends Model
{
    protected $table = 'bumi_deposit_bank_account';
    public $timestamps = false;

    use ByTrait, HistoryTrait, MasterTrait;
}
