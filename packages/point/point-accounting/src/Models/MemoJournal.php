<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Point\Framework\Traits\FormulirTrait;
use Point\PointAccounting\Vesa\MemoJournalVesa;

class MemoJournal extends Model
{
    protected $table = 'point_accounting_memo_journal';
    public $timestamps = false;

    use FormulirTrait, MemoJournalVesa;

    /**
     * Inject function when saving
     *
     * @param array $options
     *
     * @return bool|null
     */
    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }

    public function scopeJoinDependencies($q)
    {
        $q->joinFormulir()->notArchived()->notCanceled()->selectOriginal();
    }

    public function memoJournalDetails()
    {
        return $this->hasMany('\Point\PointAccounting\Models\MemoJournalDetail', 'memo_journal_id');
    }

    public static function bladeEmail()
    {
        return 'point-accounting::emails.accounting.point.approval.memo-journal';
    }
}
