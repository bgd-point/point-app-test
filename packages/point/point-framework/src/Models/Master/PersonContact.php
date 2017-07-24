<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class PersonContact extends Model
{
    protected $table = 'person_contact';

    use HistoryTrait, ByTrait;
}
