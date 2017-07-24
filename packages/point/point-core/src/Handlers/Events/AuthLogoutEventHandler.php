<?php

namespace Point\Core\Handlers\Events;

use Jenssegers\Agent\Agent;

class AuthLogoutEventHandler
{
    public function handle()
    {
        if (auth()->check()) {
            $agent = new Agent();
            timeline_publish('auth.logout', trans('core::core/global.auth.logout', [
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
}
