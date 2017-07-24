<?php

namespace Point\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Vesa extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vesa';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function taskable()
    {
        return $this->morphTo();
    }
}
