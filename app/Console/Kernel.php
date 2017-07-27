<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
        Commands\CleanFile::class,
        Commands\SeedDummy::class,
        Commands\ResetDatabase::class,
        Commands\SettingResetDatabase::class,
        Commands\Republish::class,
        Commands\ResetMenu::class,
        Commands\UpdateRoleAdmin::class,
        Commands\EmailApproval::class,
        Commands\ClearTransaction::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
                 ->hourly();
    }
}
