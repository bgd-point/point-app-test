<?php

namespace Point\Core\Http\Controllers;

use Point\Core\Helpers\TempDataHelper;
use Point\Core\Models\Temp;

class TempController extends Controller
{
    /**
     * Add row into temp table
     */
    public function add()
    {
        $temp = new Temp;
        $temp->name = app('request')->get('name');
        $temp->user_id = app('request')->get('user_id');
        $temp->keys = serialize(app('request')->get('keys'));
        $temp->save();

        return $temp;
    }

    /**
     * Clear temporary data in temp table
     *
     * @param $name
     * @param $user_id
     * @param $redirect_to
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear($name, $user_id, $redirect_to)
    {
        TempDataHelper::clear($name, $user_id);

        gritter_success('temporary has been cleared');

        if (!$redirect_to) {
            return redirect()->back();
        }

        return redirect($redirect_to);
    }
}
