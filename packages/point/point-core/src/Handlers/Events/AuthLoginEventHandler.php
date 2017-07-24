<?php

namespace Point\Core\Handlers\Events;

use Jenssegers\Agent\Agent;

class AuthLoginEventHandler
{
    public function handle()
    {
        $agent = new Agent();
        timeline_publish('auth.login', trans('core::core/global.auth.login', [
            'user_name' => auth()->user()->name,
            'user_ip' => \Request::getClientIp(),
            'user_device' => $agent->device(),
            'user_platform' => $agent->platform(),
            'user_platform_version' => $agent->version($agent->platform()),
            'user_browser' => $agent->browser(),
            'user_browser_version' => $agent->version($agent->browser())
        ]));
    }
}
