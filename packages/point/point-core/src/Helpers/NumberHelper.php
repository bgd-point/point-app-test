<?php

namespace Point\Core\Helpers;

class NumberHelper
{

    /**
     * Convert input format to database
     * @param  string $number
     * @return double
     */
    public static function formatDB($number)
    {
        $number = str_replace(',', '', $number);
        return $number;
    }

    /**
     * Convert number from database to price format
     * @param  double $number
     * @param  int $decimal =2
     * @return double
     */
    public static function formatPrice($number, $decimal = 2)
    {
        return number_format($number, $decimal, '.', ',');
    }

    /**
     * Convert number from database to quantity format
     * @param  double $number
     * @param  int $decimal =2
     * @return double
     */
    public static function formatQuantity($number, $decimal = 2)
    {
        return number_format($number, $decimal, '.', ',');
    }

    /**
     * Convert number from database to accounting format
     * @param  double $number
     * @return string
     */
    public static function formatAccounting($number)
    {
        $original_number = $number;
        $number = number_format(abs($number), 2, '.', ',');
        return $original_number < 0 ? "(" . $number . ")" : $number;
    }

    /**
     * Convert bytes to another unit "kb", "mb", "gb", "tb"
     * @param  int $bytes
     * @return string
     */
    public static function bytesConverter($bytes)
    {
        if ($bytes == 0) {
            return 0;
        }
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 0))) . " " . $arItem["UNIT"];

                break;
            }
        }
        return $result;
    }


    /**
     * @param $number
     *
     * @return string
     */
    public static function toText($number)
    {
        $buf = "";
        $str = $number . "";
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $buf = trim($buf) . " " . self::numberToText($i, $str, $len);
        }
        return trim($buf);
    }

    private static function numberIsValid($str, $from, $to, $min = 1, $max = 9)
    {
        $val = false;
        $from = ($from < 0) ? 0 : $from;
        for ($i = $from; $i < $to; $i++) {
            if (((int) $str{$i} >= $min) && ((int) $str{$i} <= $max)) {
                $val = true;
            }
        }
        return $val;
    }

    private static function numberToText($i, $str, $len)
    {
        $numA = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan");
        $numB = array("", "se", "dua ", "tiga ", "empat ", "lima ", "enam ", "tujuh ", "delapan ", "sembilan ");
        $numC = array("", "satu ", "dua ", "tiga ", "empat ", "lima ", "enam ", "tujuh ", "delapan ", "sembilan ");
        $numD = array(0 => "puluh", 1 => "belas", 2 => "ratus", 4 => "ribu", 7 => "juta", 10 => "milyar", 13 => "triliun");
        $buf = "";
        $pos = $len - $i;
        switch ($pos) {
            case 1:
                if (! self::numberIsValid($str, $i - 1, $i, 1, 1)) {
                    $buf = $numA[(int) $str{$i}];
                }
                break;
            case 2: case 5: case 8: case 11: case 14:
            if ((int) $str{$i} == 1) {
                if ((int) $str{$i + 1} == 0) {
                    $buf = ($numB[(int) $str{$i}]) . ($numD[0]);
                } else {
                    $buf = ($numB[(int) $str{$i + 1}]) . ($numD[1]);
                }
            } elseif ((int) $str{$i} > 1) {
                $buf = ($numB[(int) $str{$i}]) . ($numD[0]);
            }
            break;
            case 3: case 6: case 9: case 12: case 15:
            if ((int) $str{$i} > 0) {
                $buf = ($numB[(int) $str{$i}]) . ($numD[2]);
            }
            break;
            case 4: case 7: case 10: case 13:
            if (self::numberIsValid($str, $i - 2, $i)) {
                if (! self::numberIsValid($str, $i - 1, $i, 1, 1)) {
                    $buf = $numC[(int) $str{$i}] . ($numD[$pos]);
                } else {
                    $buf = $numD[$pos];
                }
            } elseif ((int) $str{$i} > 0) {
                if ($pos == 4) {
                    $buf = ($numB[(int) $str{$i}]) . ($numD[$pos]);
                } else {
                    $buf = ($numC[(int) $str{$i}]) . ($numD[$pos]);
                }
            }
            break;
        }
        return $buf;
    }
}
