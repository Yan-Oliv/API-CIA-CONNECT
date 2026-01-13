<?php

namespace App\Console\Commands;

use App\Domain\Consultas\ConsultaMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use PDF;

class BackupConsultasCommand extends Command
{
    protected $signature = 'backup:consultas';
    protected $description = 'Gera um PDF com os dados da tabela consultas, envia por email e limpa a tabela';

    public function handle()
    {
	Log::info('[BackupConsultas] Executado pelo scheduler', [
             'hora' => now()->toDateTimeString(),
             'total_consultas' => DB::table('consultas')->count(),
        ]);

        try {
            $consultas = DB::table('consultas')->get();

            if ($consultas->isEmpty()) {
                $this->info('Nenhuma consulta encontrada para backup.');
                return Command::SUCCESS;
            }

            $clientes = DB::table('clientes')->pluck('nome', 'id');
            $usuarios = DB::table('users')->get()->keyBy('id');
            $filiais  = DB::table('filiais')->pluck('filial', 'id');

            $dados = [];
            $porEmpresa = [];
            $porGR = [];
            $porStatus = [];
            $porFilial = [];

            foreach ($consultas as $c) {
                $empresa = $clientes[$c->cliente_id] ?? 'Desconhecida';
                $usuario = $usuarios[$c->user_id] ?? null;

                $filial = ($usuario && $usuario->filial_id && isset($filiais[$usuario->filial_id]))
                    ? $filiais[$usuario->filial_id]
                    : 'Desconhecida';

		$gr = ConsultaMapper::gr($c);

                $porEmpresa[$empresa] = ($porEmpresa[$empresa] ?? 0) + 1;
                $porGR[$gr] = ($porGR[$gr] ?? 0) + 1;
                $porStatus[$c->status] = ($porStatus[$c->status] ?? 0) + 1;
                $porFilial[$filial] = ($porFilial[$filial] ?? 0) + 1;

                $dados[] = [
                    'empresa'   => $empresa,
                    'motorista' => $c->motorista,
                    'gr'        => $gr,
                    'status'    => $c->status,
                    'consulta'  => $c->consulta,
                    'destino'   => $c->destino,
                ];
            }

            $pdf = PDF::loadView('pdf.backup_consultas', [
                'dados'       => $dados,
                'porEmpresa'  => $porEmpresa,
                'porGR'       => $porGR,
                'porStatus'   => $porStatus,
                'porFilial'   => $porFilial,
                'data'        => now()->format('d/m/Y H:i'),
                'total'       => count($dados),
            ]);

            $fileName = 'backup_consultas_' . now()->format('Y_m_d_His') . '.pdf';
            $filePath = storage_path("app/backups/{$fileName}");

            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            file_put_contents($filePath, $pdf->output());

            $emailEnviado = $this->enviarEmailComAnexo($filePath, $fileName);
            $telegramEnviado = $this->enviarParaTelegram($filePath);

            if ($emailEnviado && $telegramEnviado) {
                DB::transaction(function () {
                    DB::table('consultas')
                        ->where('status', 'ENVIADO')
                        ->delete();
                });

                Log::info('[Backup] Registros excluídos após envio bem-sucedido.');
            } else {
                Log::warning('[Backup] Falha no envio. Dados preservados.');
            }

	    return Command::SUCCESS;

        } catch (\Throwable $e) {
            Log::error('[BackupConsultasCommand] Erro inesperado: ', [
		'message' => $e->getMessage(),
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'trace' => $e->getTraceAsString(),
	   ]);

	   return Command::FAILURE;
        }
    }

    private function enviarEmailComAnexo(string $filePath, string $fileName): bool
    {
        $emailGrupos = [
            'Gerentes' => ['eder.catalao@hotmail.com'],
            'Gestores' => ['eder.henrique@ciacargas.com.br'],
            'Operacional' => [
                'filialgo@ciacargas.com.br',
                'ciacargasgo2@ciacargas.com.br',
		'luiz.henrique@ciacargas.com.br',
            ],
            'Administrador' => ['yanoliveiragm@gmail.com'],
        ];

        $todosEnviados = true;

        foreach ($emailGrupos as $emails) {
            foreach ($emails as $email) {
                try {
                    Mail::raw(
			"Segue em anexo o backup diário das consultas", 
			function ($message) use ($email, $filePath, $fileName) {
                        $message->to($email)
                            ->subject('⚠️🚨 BACKUP DIÁRIO CONSULTAS - CIA CARGAS 🚨⚠️')
                            ->attach($filePath, [
                                'as' => $fileName,
                                'mime' => 'application/pdf',
                            ]);
                    });
                } catch (\Throwable $e) {
                    Log::error("[Backup][Email] Falha ao enviar para {$email}: ", [
			'exception' => $e,
		    ]);
                    $todosEnviados = false;
                }
            }
        }

        if ($todosEnviados) {
            Log::info("[Backup] Todos e-mails enviados com sucesso.");
        }

        return $todosEnviados;
    }

    private function enviarParaTelegram(string $filePath): bool
    {
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('TELEGRAM_CHAT_ID');

            if (!$botToken || !$chatId) {
                Log::warning('[Telegram] Token ou Chat ID não definidos.');
                return false;
            }

            $response = Http::attach(
                'document',
	 	 file_get_contents($filePath),
	 	 basename($filePath)
            )->post("https://api.telegram.org/bot{$botToken}/sendDocument", [
                'chat_id' => $chatId,
                'caption' => '📄 Backup diário das consultas - ' . now()->format('d/m/Y H:i'),
            ]);

            if ($response->successful()) {
                Log::info('[Telegram] Backup enviado com sucesso.');
                return true;
    	    }
        } catch (\Throwable $e) {
            Log::error('[Telegram] Erro ao enviar: ', [
		'exception' => $e,
	    ]);
            return false;
        }
    }
}
