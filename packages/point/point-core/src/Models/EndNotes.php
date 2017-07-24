<?php

namespace Point\Core\Models;

use Illuminate\Database\Eloquent\Model;

class EndNotes extends Model
{
    public $timestamps = false;
    
    protected $table = 'end_notes';

    public static function exist($feature)
    {
        if (EndNotes::where('feature', '=', $feature)->first()) {
            return true;
        }

        return false;
    }

    public static function insert($feature)
    {
        if (! self::exist($feature)) {
            $notes = new EndNotes;
            $notes->feature = $feature;
            $notes->notes = null;
            $notes->save();
        }
    }
}
