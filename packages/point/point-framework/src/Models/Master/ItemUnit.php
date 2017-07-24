<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\MasterTrait;

class ItemUnit extends Model
{
    protected $table = 'item_unit';

    use HistoryTrait, ByTrait, MasterTrait;
}
