<?php

namespace Point\BumiDeposit\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;

class DepositGroup extends Model
{
    protected $table = 'bumi_deposit_group';
    public $timestamps = false;

    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%'. $search .'%');
    }

    public function deposits($owner_id = 0, $bank_id = 0, $status = 'ongoing')
    {
        $q = $this->hasMany('Point\BumiDeposit\Models\Deposit', 'deposit_group_id')->where('deposit_group_id', '=', $this->id)->joinFormulir()->notArchived()->active();

        if ($owner_id > 0) {
            $q->where('deposit_owner_id', '=', $owner_id);
        }

        if ($bank_id > 0) {
            $q->where('deposit_bank_id', '=', $bank_id);
        }

        if ($status == 'ongoing' || $status == null) {
            $q->where('formulir.form_status', '=', 0);
        } elseif ($status == 'closed') {
            $q->where('formulir.form_status', '=', 1);
        }

        return $q;
    }

    use ByTrait, HistoryTrait, MasterTrait;
}
