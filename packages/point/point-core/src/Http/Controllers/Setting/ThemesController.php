<?php

namespace Point\Core\Http\Controllers\Setting;

use Point\Core\Http\Controllers\Controller;

class ThemesController extends Controller
{
    /**
     * Themes option
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('core::app.settings.themes');
    }
}
