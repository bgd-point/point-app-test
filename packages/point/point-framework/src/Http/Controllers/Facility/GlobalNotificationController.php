<?php

namespace Point\Framework\Http\Controllers\Facility;

use Illuminate\Support\Facades\Redis;
use Point\Framework\Http\Controllers\Controller;

class GlobalNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view = view('framework::app.facility.global-notification.index');
        return $view;
    }

    public function apply()
    {
        $data = [
            'event' => 'GlobalNotification',
            'data' => [
                'user_id' => auth()->user()->id,
                'message' => \Input::get('message')
            ]
        ];

        Redis::publish(config('point.client.channel'), json_encode($data));

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Sent Notification Success'
        );

        return $response;
    }
}
