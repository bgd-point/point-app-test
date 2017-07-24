<?php

namespace Point\Framework\Http\Controllers\Setting;

use Point\Framework\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * Send global notification
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('framework::app.settings.notification');
    }
}
