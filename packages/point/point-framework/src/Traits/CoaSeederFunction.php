<?php

namespace Point\Framework\Traits;

use Point\Framework\Models\Master\Coa;

trait CoaSeederFunction
{
    public static function exist($name)
    {
        if (Coa::where('name', '=', $name)->first()) {
            return true;
        }

        return false;
    }

    public static function insert($coa_category_id, $name, $has_subledger = false, $subledger_type = null, $coa_group_id = null, $notes = null)
    {
        if (! self::exist($name)) {
            $coa = new Coa;
            $coa->coa_category_id = $coa_category_id;
            $coa->coa_group_id = $coa_group_id;
            $coa->name = $name;
            $coa->has_subledger = $has_subledger;
            $coa->notes = $notes;
            $coa->created_by = 1;
            $coa->updated_by = 1;
            $coa->subledger_type = $subledger_type;
            $coa->save();

            return $coa;
        }
    }
}
