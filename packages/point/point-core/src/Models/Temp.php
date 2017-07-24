<?php

namespace Point\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Temp extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'temp';


    /**
     * @param array $options
     */
    public function save(array $options = array())
    {
        if (!$this->user_id) {
            $this->user_id = auth()->user()->id;
        }

        parent::save($options);
    }

    /**
     *
     */
    public function user()
    {
        $this->belongsTo('\Point\Core\Models\User', 'user_id');
    }
}
