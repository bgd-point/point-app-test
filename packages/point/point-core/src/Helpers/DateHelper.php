<?php

namespace Point\Core\Helpers;

use Point\Core\Models\Setting;

class DateHelper
{

    /**
     * convert date input to database format
     * @param  datetime $date
     * @param  string $hour ='original'
     * @return datetime
     */
    public static function formatDB($date, $hour = 'original')
    {
        // select format from database
        $date_input = Setting::where('name', '=', 'date-input')->first()->value;

        // convert
        if ($date_input == 'd-m-y') {
            $array = explode('-', $date);
        } elseif ($date_input == 'd-m-Y') {
            $array = explode('-', $date);
        } elseif ($date_input == 'd/m/y') {
            $array = explode('/', $date);
        } elseif ($date_input == 'd/m/Y') {
            $array = explode('/', $date);
        }

        $date = $array[2] . '-' . $array[1] . '-' . $array[0];

        // return database format
        if ($hour == 'start') {
            return date('Y-m-d 00:00:00', strtotime($date));
        } elseif ($hour == 'end') {
            return date('Y-m-d 23:59:59', strtotime($date));
        } elseif ($hour != 'original') {
            return date('Y-m-d ' . $hour, strtotime($date));
        }

        return date('Y-m-d H:i:s', strtotime($date));
    }

    /**
     * display date for view
     * @param  datetime $date
     * @param  boolean $time =false [display time]
     * @return datetime
     */
    public static function formatView($date, $time = false)
    {
        // select format from database
        $date_input = Setting::where('name', '=', 'date-show')->first()->value;
        if ($time === true) {
            return date($date_input . ' H:i', strtotime($date));
        }

        return date($date_input, strtotime($date));
    }

    /**
     * default date for input mask
     * @return string [masking date input]
     */
    public static function formatMasking()
    {
        // select format from database
        $date_input = Setting::where('name', '=', 'date-input')->first()->value;

        // return format
        if ($date_input == 'd-m-y') {
            return 'dd-mm-yy';
        } elseif ($date_input == 'd-m-Y') {
            return 'dd-mm-yyyy';
        } elseif ($date_input == 'd/m/y') {
            return 'dd/mm/yy';
        } elseif ($date_input == 'd/m/Y') {
            return 'dd/mm/yyyy';
        }
    }

    /**
     * default format value for edit form
     * @return string [date() parameter format]
     */
    public static function formatGet()
    {
        return Setting::where('name', '=', 'date-input')->first()->value;
    }
}
