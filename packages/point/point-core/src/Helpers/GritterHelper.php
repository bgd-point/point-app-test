<?php

namespace Point\Core\Helpers;

use Illuminate\Support\Facades\Facade;

/**
 * Class Gritter
 * @package Point\Core\Helpers
 */
class GritterHelper extends Facade
{
    public static function set($title, $message, $sticky = 'true')
    {
        for ($i = 0; $i <= 10; $i++) {
            if (!session()->has('gritter_message_' . $i)) {
                session()->flash('gritter_title_' . $i, $title);
                session()->flash('gritter_message_' . $i, $message);
                session()->flash('gritter_sticky_' . $i, self::castToString($sticky));
                break;
            }
        }
    }

    public static function success($message, $sticky = 'true')
    {
        for ($i = 0; $i <= 10; $i++) {
            if (!session()->has('gritter_message_' . $i)) {
                session()->flash('gritter_title_' . $i, 'Success');
                session()->flash('gritter_message_' . $i, $message);
                session()->flash('gritter_sticky_' . $i, self::castToString($sticky));
                break;
            }
        }
    }

    public static function info($message, $sticky = 'true')
    {
        for ($i = 0; $i <= 10; $i++) {
            if (!session()->has('gritter_message_' . $i)) {
                session()->flash('gritter_title_' . $i, 'Info');
                session()->flash('gritter_message_' . $i, $message);
                session()->flash('gritter_sticky_' . $i, self::castToString($sticky));
                break;
            }
        }
    }

    public static function error($message, $sticky = 'true')
    {
        for ($i = 0; $i <= 10; $i++) {
            if (!session()->has('gritter_message_' . $i)) {
                session()->flash('gritter_title_' . $i, 'Error');
                session()->flash('gritter_message_' . $i, $message);
                session()->flash('gritter_sticky_' . $i, self::castToString($sticky));
                break;
            }
        }
    }

    private static function castToString($var)
    {
        if (is_string($var)) {
            return $var;
        }

        if ($var == true) {
            return 'true';
        }

        if ($var == false) {
            return 'false';
        }
    }

    protected static function getFacadeAccessor()
    {
        return 'gritter';
    }
}
