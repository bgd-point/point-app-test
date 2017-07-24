<?php

namespace Point\Framework\Helpers;

use Illuminate\Support\Facades\DB;
use Point\Framework\Models\FormulirNumber;

class FormulirNumberHelper
{
    public static function create($name, $code)
    {
        if (!self::exist($name)) {
            DB::table('formulir_number')->insert(['name' => $name, 'code' => $code]);
        }
    }

    private static function exist($value)
    {
        return FormulirNumber::where('name', '=', $value)->first();
    }
}
