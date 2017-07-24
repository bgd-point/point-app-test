<?php

namespace Point\Core\Helpers;

use Point\Core\Models\Timeline;
use Illuminate\Support\Facades\Redis;

class TimelineHelper
{

    /**
     * Publish data to redis for real time
     *
     * @param $action
     * @param $message
     */
    public static function publish($action, $message, $user_id = '')
    {
        if ($user_id == '') {
            $user_id = auth()->user()->id;
        }

        $timeline = new Timeline;
        $timeline->user_id = $user_id;
        $timeline->action = $action;
        $timeline->message = $message;
        $timeline->save();

        $data = [
            'event' => 'Timeline',
            'data' => [
                'user_id' => $user_id,
                'message' => (string) view('core::app._timeline')->with('timeline', $timeline)
            ]
        ];

        Redis::publish(config('point.client.channel'), json_encode($data));
    }
}
