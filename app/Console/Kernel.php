<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\BackupConsultasCommand::class,
        \App\Console\Commands\DeleteOldDoneLembretes::class, 
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:consultas')->dailyAt('00:00');
        $schedule->command('rastreio:delete-old-finalized')->dailyAt('02:00');
        $schedule->command('lembrete:delete-old-done')->dailyAt('03:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
