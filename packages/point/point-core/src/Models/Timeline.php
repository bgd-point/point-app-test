<?php

namespace Point\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class Timeline extends Model
{
    use ByTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'timeline';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Point\Core\Models\User', 'user_id');
    }
}
