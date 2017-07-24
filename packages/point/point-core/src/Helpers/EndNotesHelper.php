<?php

namespace Point\Core\Helpers;

use Point\Core\Models\EndNotes;

class EndNotesHelper
{
    public static function getNotes($feature)
    {
        $notes = EndNotes::where('feature', $feature)->first();
        if (! $notes) {
            return null;
        }

        return $notes->notes;
    }
}
