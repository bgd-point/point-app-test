<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class FormulirLock extends Model
{
    protected $table = 'formulir_lock';
    public $timestamps = false;

    use HistoryTrait, ByTrait;

    public function locked()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'locked_id');
    }

    public function locking()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'locking_id');
    }
}
