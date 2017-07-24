<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class AllocationReport extends Model
{
    protected $table = 'allocation_report';
    public $timestamps = false;

    use FormulirTrait;

    public function scopeJoinAllocation($q)
    {
        $q->join('allocation', 'allocation.id', '=', $this->table.'.allocation_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }
}
