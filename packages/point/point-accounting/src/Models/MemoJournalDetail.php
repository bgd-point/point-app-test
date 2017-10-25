<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class MemoJournalDetail extends Model
{
    protected $table = 'point_accounting_memo_journal_detail';
    public $timestamps = false;

    use ByTrait;

    public function memoJournal()
    {
        return $this->belongsTo('\Point\PointAccounting\Models\MemoJournal', 'memo_journal_id');
    }

    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function formulir()
    {
        return $this->belongsTo('Point\Framework\Models\Formulir', 'form_journal_id');
    }

    public function reference()
    {
        return $this->belongsTo('Point\Framework\Models\Formulir', 'form_reference_id');
    }
}
