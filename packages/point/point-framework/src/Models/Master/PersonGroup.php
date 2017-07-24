<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class PersonGroup extends Model
{
    protected $table = 'person_group';
    public $timestamps = false;

    use HistoryTrait, ByTrait;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function person()
    {
        return $this->hasMany('Point\Framework\Models\Master\Person', 'person_group_id');
    }
}
