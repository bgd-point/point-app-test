<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

class EmailHistory extends Model
{
    protected $table = 'email_history';
    public $timestamps = false;

    public function formulir()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir');
    }

    public function person()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Person', 'recipient');
    }

    public function user()
    {
    	return $this->belongsTo('Point\Core\Models\User', 'sender');
    }
}
