<?php

namespace App\Console;

use App\Jobs\Holiday\DispatchProlongHolidays;
use App\Jobs\Media\DispatchMakeWebPs;
use App\Jobs\Notification\DispatchNotifications;
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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new DispatchMakeWebPs(10))->everyFifteenMinutes();
        $schedule->job(new DispatchNotifications(100))->everyMinute();
        $schedule->job(new DispatchProlongHolidays())->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
