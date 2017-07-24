<?php

namespace Point\Core\Http\Controllers\Setting;

use Point\Core\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * Send global notification
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('core::app.settings.notification');
    }
}
