<?php

namespace Point\Core\Helpers;

use Illuminate\Support\Facades\Facade;
use Point\Core\Models\Temp;

class TempDataHelper extends Facade
{
    /**
     * @param $name
     * @param $user_id
     */
    public static function clear($name, $user_id)
    {
        Temp::where('user_id', '=', $user_id)->where('name', '=', $name)->delete();
    }

    /**
     * @param $rowid
     * @return mixed
     */
    public static function find($rowid)
    {
        $temp = Temp::find($rowid);
        $array = unserialize($temp->keys);
        $array['rowid'] = $temp->id;
        return $array;
    }

    /**
     * @param $name
     * @param $user_id
     *
     * @return mixed
     */
    public static function getPagination($name, $user_id)
    {
        return Temp::where('user_id', '=', $user_id)->where('name', '=', $name)->paginate(100);
    }

    /**
     * @param       $name
     * @param       $user_id
     * @param array $key
     * @param array $value
     *
     * @return mixed|null
     */
    public static function searchKeyValue($name, $user_id, $key = [], $value = [])
    {
        $temps = self::get($name, $user_id, ['is_pagination' => false]);
        $count = count($key);
        foreach ($temps as $temp) {
            $status = 0;
            for ($i = 0; $i < count($key); $i++) {
                if ($temp[$key[$i]] == $value[$i]) {
                    $status++;
                }
            }

            if ($count == $status) {
                return $temp;
            }
        }
        return null;
    }

    /**
     * @param       $name
     * @param       $user_id
     * @param array $option
     *
     * @return array
     */
    public static function get($name, $user_id, $option = ['is_pagination' => false])
    {
        $temps = Temp::where('user_id', '=', $user_id)->where('name', '=', $name);
        if ($option['is_pagination']) {
            $temps = $temps->paginate(100);
        } else {
            $temps = $temps->get();
        }
        $array = [];
        $count = 0;
        foreach ($temps as $temp) {
            $key = unserialize($temp->keys);
            array_push($array, $key);
            $array[$count++]['rowid'] = $temp->id;
        }
        return $array;
    }

    public static function removeRowHaveKeyValue($name, $user_id, $key, $value)
    {
        $temp = self::getAllRowHaveKeyValue($name, $user_id, $key, $value);
        for ($i = 0; $i < count($temp); $i++) {
            self::remove($temp[$i]['rowid']);
        }
        return true;
    }

    public static function getAllRowHaveKeyValue($name, $user_id, $key, $value)
    {
        $temp = self::get($name, $user_id, ['is_pagination' => false]);
        $row_id = [];

        for ($i = 0; $i < count($temp); $i++) {
            if ($temp[$i][$key] == $value) {
                array_push($row_id, $temp[$i]);
            }
        }
        return $row_id;
    }

    /**
     * @param $id
     * @return bool|null
     * @throws \Exception
     */
    public static function remove($id)
    {
        return Temp::find($id)->delete();
    }

    protected static function getFacadeAccessor()
    {
        return 'tempData';
    }
}
