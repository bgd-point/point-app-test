<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Handler\SlackHandler;
use Monolog\Logger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // SLACK NOTIFICATION FOR MONOLOG ERROR
        $monolog = \Log::getMonolog();
        $slackHandler = new SlackHandler(env('SLACK_TOKEN'), env('SLACK_CHANNEL'), 'Monolog', true, null, Logger::ERROR);
        $monolog->pushHandler($slackHandler);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
//        $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);
    }
}
