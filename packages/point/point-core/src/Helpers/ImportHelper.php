<?php

namespace Point\Core\Helpers;

use Point\Core\Exceptions\PointException;

class ImportHelper
{

    /**
     * Validate file upload from user
     * must be formatted as xls or xlsx
     *
     * @return string
     */
    public static function xlsValidate()
    {
        $file = $_FILES['file']['name'];
        $file_part = pathinfo($file);
        $extension = $file_part['extension'];
        $support_extention = array('xls', 'xlsx');
        if (! in_array($extension, $support_extention)) {
            throw new PointException('FILE FORMAT NOT ACCEPTED, PLEASE USE XLS OR XLSX EXTENTION');
        }
    }
}
