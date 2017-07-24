<?php

namespace Point\PointManufacture\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;

class Machine extends Model
{
    use HistoryTrait, ByTrait, MasterTrait;

    protected $table = 'point_manufacture_machine';
    public $timestamps = true;

    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%' . $search . '%');
    }
}
