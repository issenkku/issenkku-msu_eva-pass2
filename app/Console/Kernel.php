<?php

namespace App\Console;

use App\Console\Commands\NotifyEndDate;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        NotifyEndDate::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('notify:enddate')->daily();
        $schedule->command('reports:update-statuses')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
