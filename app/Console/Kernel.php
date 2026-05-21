<?php

// ไฟล์คลาสของระบบ: app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\NotifyEndDate::class,
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
