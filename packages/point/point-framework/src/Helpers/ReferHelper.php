<?php

namespace Point\Framework\Helpers;

use Point\Core\Exceptions\PointException;
use Point\Framework\Models\Refer;

class ReferHelper
{
    public static function create($by_type, $by_id, $to_type, $to_id, $to_parent_type, $to_parent_id, $value)
    {
        $refer = new Refer;
        $refer->by_type = $by_type;
        $refer->by_id = $by_id;
        $refer->to_type = $to_type;
        $refer->to_id = $to_id;
        $refer->to_parent_type = $to_parent_type;
        $refer->to_parent_id = $to_parent_id;
        $refer->value = $value;
        $refer->save();
    }

    public static function cancel($to_parent_type, $to_parent_id)
    {
        $refers = Refer::where('to_parent_type', '=', $to_parent_type)
            ->where('to_parent_id', '=', $to_parent_id)
            ->where('status', '=', true)
            ->get();

        foreach ($refers as $refer) {
            $refer->status = 'false';
            $refer->save();
        }
    }

    public static function remaining($by_type, $by_id, $original_value)
    {
        $value = Refer::where('by_type', '=', $by_type)
            ->where('by_id', '=', $by_id)
            ->where('status', '=', true)
            ->sum('value');

        if ($value >= 0) {
            return $original_value - $value;
        }

        return $original_value + $value;
    }

    public static function remainingBeforeEdit($reference_type, $reference_id, $form_type, $form_id)
    {
        $refer = self::getReferTo($reference_type, $reference_id, $form_type, $form_id);
        $refer->quantity;

        $value = Refer::where('by_type', '=', $by_type)
            ->where('by_id', '=', $by_id)
            ->where('status', '=', true)
            ->sum('value');

        if ($value >= 0) {
            return $original_value - $value;
        }

        return $original_value + $value;
    }

    public static function getReferTo($by_type, $by_id, $to_parent_type, $to_parent_id)
    {
        $refer = Refer::where('by_type', '=', $by_type)
            ->where('by_id', '=', $by_id)
            ->where('to_parent_type', '=', $to_parent_type)
            ->where('to_parent_id', '=', $to_parent_id)
            ->first();

        if ($refer) {
            $refer_to = $refer->to_type;
            return $refer_to::find($refer->to_id);
        }
    }

    public static function getReferBy($to_type, $to_id, $to_parent_type, $to_parent_id)
    {
        $refer = Refer::where('to_type', '=', $to_type)
            ->where('to_id', '=', $to_id)
            ->where('to_parent_type', '=', $to_parent_type)
            ->where('to_parent_id', '=', $to_parent_id)
            ->first();

        if ($refer) {
            $refer_by = $refer->by_type;
            return $refer_by::find($refer->by_id);
        }
    }

    public static function getRefers($to_parent_type, $to_parent_id)
    {
        $refers = Refer::where('to_parent_type', '=', $to_parent_type)
            ->where('to_parent_id', '=', $to_parent_id)
            ->get();

        return $refers;
    }

    public static function getRefersId($by_type, $to_parent_type, $to_parent_id)
    {
        $refers = Refer::where('to_parent_type', '=', $to_parent_type)
            ->where('to_parent_id', '=', $to_parent_id)
            ->where('by_type', '=', $by_type)
            ->lists('by_id');

        return $refers;
    }

    public static function closeStatus($by_type, $by_id, $origin_value, $edited_value = 0)
    {
        $value = 0;
        $refers = Refer::where('by_type', '=', $by_type)
            ->where('by_id', '=', $by_id)
            ->where('status', '=', true)
            ->get();

        foreach ($refers as $refer) {
            $value += $refer->value;
        }

        $value -= $edited_value;

        if ($value > $origin_value) {
            throw new PointException('ERROR : EXCESS AMOUNT');
        }

        if ($value == $origin_value) {
            return true;
        }

        return false;
    }
}
