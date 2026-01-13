<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
 * Definindo um comando personalizado
 * Isso é semelhante ao comando 'inspire', mas com a lógica para o prune do Sanctum.
 */
Artisan::command('prune:sanctum', function () {
    // Chama o comando do Sanctum para limpar os tokens expirados
    Artisan::call('sanctum:prune-expired --hours=24');
    $this->info('Tokens expirados do Sanctum foram limpos!');
})->purpose('Limpar tokens expirados do Sanctum');

/*
 * Definindo o comando inspire original para referência.
 * Este comando é apenas um exemplo, você pode mantê-lo ou removê-lo conforme necessário.
 */
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('backup:consultas')->dailyAt('00:00');
