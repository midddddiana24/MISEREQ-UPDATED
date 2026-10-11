<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

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
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            Cache::put('system:scheduler_last_seen', now()->toIso8601String(), now()->addMinutes(10));
        })
            ->name('system-scheduler-heartbeat')
            ->everyMinute()
            ->onOneServer();

        $schedule->command('system:backup --quiet')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Laptops can sleep through 02:00. Once the scheduler resumes, create
        // the missed daily archive unless another backup already covers it.
        $schedule->command('system:backup --if-due --quiet')
            ->everyFifteenMinutes()
            ->unlessBetween('01:45', '02:14')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('queue:prune-failed --hours=168')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Note: queue:work is now run by the queue supervisor (scripts/queue-worker-supervisor.ps1)
        // Do NOT schedule queue:work here - it's inefficient and conflicts with the supervisor
        // See: docs/QUEUE-WORKER-SETUP.md

        $schedule->command('system:privacy-prune')
            ->weeklyOn(1, '04:00')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('files:cleanup-orphaned')
            ->dailyAt('03:30')
            ->withoutOverlapping()
            ->onOneServer()
            ->sendOutputTo(storage_path('logs/file-cleanup.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
