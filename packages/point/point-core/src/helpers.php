<?php

use Point\Core\Helpers\DateHelper;
use Point\Core\Helpers\EndNotesHelper;
use Point\Core\Helpers\GritterHelper;
use Point\Core\Helpers\NumberHelper;
use Point\Core\Helpers\PermissionHelper;
use Point\Core\Helpers\PluginHelper;
use Point\Core\Helpers\TimelineHelper;

/**
 * Number Helper global function
 */

if (! function_exists('number_format_db')) {
    /**
     * Convert input format to database
     * @param $number
     * @return float
     */
    function number_format_db($number)
    {
        return NumberHelper::formatDB($number);
    }
}

if (! function_exists('number_format_price')) {
    /**
     * Convert number from database to price format
     * @param $number
     * @param int $decimal
     * @return float
     */
    function number_format_price($number, $decimal = 2)
    {
        return NumberHelper::formatPrice($number, $decimal);
    }
}

if (! function_exists('number_format_quantity')) {
    /**
     * @param $number
     * @param int $decimal
     * @return float
     */
    function number_format_quantity($number, $decimal = 2)
    {
        return NumberHelper::formatQuantity($number, $decimal);
    }
}

if (! function_exists('number_format_accounting')) {
    /**
     * @param $number
     * @return string
     */
    function number_format_accounting($number)
    {
        return NumberHelper::formatAccounting($number);
    }
}

if (! function_exists('bytes_converter')) {
    /**
     * Convert bytes to another unit "kb", "mb", "gb", "tb"
     * @param $bytes
     * @return string
     */
    function bytes_converter($bytes)
    {
        return NumberHelper::bytesConverter($bytes);
    }
}

if (! function_exists('number_to_text')) {
    /**
     * @param $number
     * @return string
     */
    function number_to_text($number)
    {
        return NumberHelper::toText($number);
    }
}

/**
 * Date Helper global function
 */

if (! function_exists('date_format_db')) {
    /**
     * @param $date
     * @param string $hour
     * @return datetime
     */
    function date_format_db($date, $hour='original')
    {
        return DateHelper::formatDB($date, $hour);
    }
}

if (! function_exists('date_format_view')) {
    /**
     * @param $date
     * @param bool $time
     * @return mixed
     */
    function date_format_view($date, $time=false)
    {
        return DateHelper::formatView($date, $time);
    }
}

if (! function_exists('date_format_masking')) {
    /**
     * @return mixed
     */
    function date_format_masking()
    {
        return DateHelper::formatMasking();
    }
}

if (! function_exists('date_format_get')) {
    /**
     * @return mixed
     */
    function date_format_get()
    {
        return DateHelper::formatGet();
    }
}

/**
 * Gritter Helper global function
 */

if (! function_exists('gritter_set')) {
    /**
     * @param $title
     * @param $message
     * @param string $sticky
     * @return mixed
     */
    function gritter_set($title, $message, $sticky = 'false')
    {
        return GritterHelper::set($title, $message, $sticky);
    }
}

if (! function_exists('gritter_success')) {
    /**
     * @param $message
     * @param string $sticky
     * @return mixed
     */
    function gritter_success($message, $sticky = 'false')
    {
        return GritterHelper::success($message, $sticky);
    }
}

if (! function_exists('gritter_info')) {
    /**
     * @param $message
     * @param string $sticky
     * @return mixed
     */
    function gritter_info($message, $sticky = 'false')
    {
        return GritterHelper::info($message, $sticky);
    }
}

if (! function_exists('gritter_error')) {
    /**
     * @param $message
     * @param string $sticky
     * @return mixed
     */
    function gritter_error($message, $sticky = 'false')
    {
        return GritterHelper::error($message, $sticky);
    }
}

/**
 * Timeline Helper global function
 */

if (! function_exists('timeline_publish')) {
    /**
     * @param $action
     * @param $message
     * @return mixed
     */
    function timeline_publish($action, $message, $user_approval = '')
    {
        return TimelineHelper::publish($action, $message, $user_approval);
    }
}

/**
 * Permission Helper global function
 */

if (! function_exists('permission_get_by_type')) {
    /**
     * @param $permission_type
     */
    function permission_get_by_type($permission_type)
    {
        return PermissionHelper::getPermissionByType($permission_type);
    }
}

if (! function_exists('permission_check')) {
    /**
     * @param $role_id
     * @param $permission_id
     * @return bool
     */
    function permission_check($role_id, $permission_id)
    {
        return PermissionHelper::check($role_id, $permission_id);
    }
}

/**
 * EndNotes Helper global function
 */

if (! function_exists('get_end_notes')) {
    /**
     * Convert input format to database
     * @param $number
     * @return float
     */
    function get_end_notes($feature)
    {
        return EndNotesHelper::getNotes($feature);
    }
}

if (! function_exists('url_logo')) {
    /**
     * get url company logo
     */
    function url_logo()
    {
        $logo_url = url('uploads/' .app('request')->project->url . '/logo/logo.png');
        if (\File::exists(public_path('uploads/' .app('request')->project->url . '/logo/logo.png')))
        {
            return url($logo_url);
        }

        return null;
    }
}

if (! function_exists('permission_check_all')) {
    /**
     * @param $permission_type
     * @return bool
     */
    function permission_check_all($role_id, $permission_type)
    {
        return PermissionHelper::checkAll($role_id, $permission_type);
    }
}

