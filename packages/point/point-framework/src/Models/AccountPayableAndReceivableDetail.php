<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayableAndReceivableDetail extends Model
{
    protected $table = 'account_payable_and_receivable_detail';
    public $timestamps = false;

    public function formulirReference()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'formulir_reference');
    }

    public function parent()
    {
        return $this->belongsTo('\Point\Framework\Models\AccountPayableAndReceivable', 'account_payable_and_receivable_id');
    }

    public function scopeJoinParent($q)
    {
        $q->join('account_payable_and_receivable', 'account_payable_and_receivable.id', '=', $this->table.'.account_payable_and_receivable_id');
    }
}
