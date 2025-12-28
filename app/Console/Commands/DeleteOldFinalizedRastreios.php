<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Funcionalidades\Rastreio;
use Carbon\Carbon;

class DeleteOldFinalizedRastreios extends Command
{
    protected $signature = 'rastreio:delete-old-finalized';
    protected $description = 'Exclui rastreios com status FINALIZADO com mais de 15 dias desde o last_update';

    public function handle()
    {
        $limite = Carbon::now()->subDays(15);

        $deletados = Rastreio::where('status', 'FINALIZADO')
            ->where('last_update', '<', $limite)
            ->delete();

        $this->info("Rastreios FINALIZADO deletados: $deletados");
    }
}
