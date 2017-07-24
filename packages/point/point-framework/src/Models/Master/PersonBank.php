<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class PersonBank extends Model
{
    protected $table = 'person_bank';

    use HistoryTrait, ByTrait;
}
