<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Funcionalidades\Lembrete;
use Carbon\Carbon;

class DeleteOldDoneLembretes extends Command
{
    protected $signature = 'lembrete:delete-old-done';
    protected $description = 'Exclui lembretes marcados como feitos há mais de 48 horas';

    public function handle()
    {
        $limite = Carbon::now()->subHours(48);

        $deletados = Lembrete::where('feito', true)
            ->where('last_update', '<', $limite)
            ->delete();

        $this->info("Lembretes feitos e antigos deletados: {$deletados}");
    }
}
