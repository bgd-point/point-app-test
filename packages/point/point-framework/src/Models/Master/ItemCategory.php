<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\MasterTrait;

class ItemCategory extends Model
{
    protected $table = 'item_category';

    use HistoryTrait, ByTrait, MasterTrait;

    /**
     * @return string
     */
    public function getCodeNameAttribute()
    {
        return '['.$this->attributes['code'] . '] ' . $this->attributes['name'];
    }
}
