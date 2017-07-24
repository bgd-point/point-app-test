<?php

namespace Point\PointManufacture\Helpers;

use Point\Core\Exceptions\PointException;
use Point\PointManufacture\Models\Machine;

class MachineHelper
{
    public static function getLastCode()
    {
        $last_machine = Machine::orderBy('id', 'desc')->first();
        $new_code = 1;
        if ($last_machine) {
            $new_code = (int)str_replace('MC-', '', $last_machine->code);
            $new_code += 1;
        }

        return 'MC-' . ($new_code);
    }
}
