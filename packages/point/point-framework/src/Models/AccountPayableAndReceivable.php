<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayableAndReceivable extends Model
{
    protected $table = 'account_payable_and_receivable';
    public $timestamps = false;

    public function accountId()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'account_id');
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public function formulirReference()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'formulir_reference_id');
    }

    public function detail()
    {
        return $this->belongsTo('\Point\Framework\Models\AccountPayableAndReceivableDetail', 'account_payable_and_receivable_id');
    }
}
