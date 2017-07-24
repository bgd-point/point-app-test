<?php

namespace Point\Core\Http\Controllers\Facility;

use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Timeline;
use Point\Core\Models\User;

class MonitoringController extends Controller
{
    public function index()
    {
        $view = view('core::app.facility.monitoring.index');
        $view->users = User::all();
        $view->timelines = Timeline::orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();
        return $view;
    }

    public function more()
    {
        $user_id = \Input::get('user_id');
        $date_input = \Input::get('date_input');
        $timelines = Timeline::where('id', '<', \Input::get('last_id'));

        if ($user_id > 0) {
            $timelines = $timelines->where(function ($q) use ($user_id) {
                $q->where('user_id', '=', $user_id)
                    ->orWhere('message', 'like', '%' . url("master/user") . '/' . $user_id . '"%');
            });
        }

        if ($date_input && !strpos($date_input, '_')) {
            $timelines = $timelines->where('created_at', '>=', date_format_db($date_input, 'start'))->where('created_at', '<=', date_format_db($date_input, 'end'));
        }

        $timelines = $timelines->orderBy('id', 'desc')->take(20)->get();

        $last_id = 0;
        foreach ($timelines as $timeline) {
            $last_id = $timeline->id;
        }

        $view = (string)view('core::app._timelines', ['timelines' => $timelines]);

        $response = array(
            'last_id' => $last_id,
            'content' => $view
        );

        return $response;
    }

    public function search()
    {
        $user_id = \Input::get('user_id');
        $date_input = \Input::get('date_input');
        $timelines = Timeline::orderBy('id', 'desc');

        if ($user_id > 0) {
            $timelines = $timelines->where('user_id', '=', $user_id);
        }

        if ($date_input && !strpos($date_input, '_')) {
            $timelines = $timelines->where('created_at', '>=', date_format_db($date_input, 'start'))->where('created_at', '<=', date_format_db($date_input, 'end'));
        }

        $timelines = $timelines->take(20)->get();

        $last_id = 0;
        foreach ($timelines as $timeline) {
            $last_id = $timeline->id;
        }

        $view = (string)view('core::app._timelines', ['timelines' => $timelines]);

        $response = array(
            'last_id' => $last_id,
            'reset' => true,
            'content' => $view
        );

        return $response;
    }
}
