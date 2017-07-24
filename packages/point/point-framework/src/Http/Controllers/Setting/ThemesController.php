<?php

namespace Point\Framework\Http\Controllers\Setting;

use Point\Framework\Http\Controllers\Controller;

class ThemesController extends Controller
{
    /**
     * Themes option
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('framework::app.settings.themes');
    }
}
