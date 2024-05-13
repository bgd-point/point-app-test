<?php

namespace Point\Framework\Helpers;

use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;

class PersonHelper
{

    /**
     * get person nomer
     * @param $person_type
     * @return string|void
     */
    public static function getCode($person_type)
    {
        $person = Person::select('code')
            ->where('person_type_id', '=', $person_type->id)
            ->orderBy('id', 'desc')
            ->first();

        if ($person) {
            $code = explode('-', $person->code);
            if (is_numeric($code[1])) {
                return $person_type->code . '-' . ($code[1] + 1);
            }
            return $person_type->code . '-' . 1;
        }

        return $person_type->code . '-1';
    }

    /**
     * Get type of contact
     *
     * @param $person_type_slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function getType($person_type_slug)
    {
        $person_type = PersonType::findSlug([$person_type_slug]);

        if (!$person_type->count()) {
            return view('core::errors.404');
        }

        return $person_type->first();
    }

    /**
     * Get filtered person by type
     *
     * @param $person_type_slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function getByType($person_type_slug = [])
    {
        $person_type = PersonType::findSlug($person_type_slug)->lists('id');

        if (!$person_type->count()) {
            return view('core::errors.404');
        }

        return Person::whereIn('person_type_id', $person_type)->get();
    }

    public static function getGroupByType($person_type_slug)
    {
        $person_type = self::getType($person_type_slug);
        $list_group = PersonGroup::where('person_type_id', $person_type->id)->get();
        
        if (!$list_group) {
            return null;
        }

        return $list_group;
    }

    public static function getUrl($id)
    {
        $person = Person::find($id);
        if (! $person) {
            return 'Person Not Found';
        }

        $link = "<a href='".url('master/contact/person/'.$id)."'>".$person->codeName."</a>";
        return $link;
    }
}
