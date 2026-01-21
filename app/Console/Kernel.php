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

        // Limpar tokens expirados diariamente à meia-noite
        $schedule->call(function () {
            \App\Models\PersonalAccessToken::where('expires_at', '<', now())->delete();
        })->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
