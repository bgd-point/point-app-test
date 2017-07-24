<?php

namespace Point\PointManufacture\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;

class Process extends Model
{
    use MasterTrait, HistoryTrait, ByTrait;

    protected $table = 'point_manufacture_process';
    public $timestamps = true;

    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%' . $search . '%');
    }
}
