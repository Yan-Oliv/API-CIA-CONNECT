<?php

namespace App\Console\Commands;

use App\Domain\Consultas\ConsultaMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class BackupConsultasCommand extends Command
{
    protected $signature = 'backup:consultas';
    protected $description = 'Gera um PDF com os dados da tabela consultas, envia por email e limpa a tabela';

    public function handle()
    {
        $startTime = microtime(true);
        Log::info('🔄 [BACKUP] ===== INICIANDO PROCESSO DE BACKUP =====', [
            'timestamp' => now()->toDateTimeString(),
            'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
        ]);

        try {
            // ETAPA 1: CONSULTA DE DADOS
            Log::info('📊 [BACKUP] ETAPA 1: Consultando dados do banco...');
            
            $consultas = DB::table('consultas')->get();
            $totalConsultas = $consultas->count();
            
            Log::info('📊 [BACKUP] Dados consultados', [
                'total_registros' => $totalConsultas,
                'tipos_status' => $consultas->pluck('status')->unique()->values()->toArray()
            ]);

            if ($consultas->isEmpty()) {
                $this->info('✅ Nenhuma consulta encontrada para backup.');
                Log::info('✅ [BACKUP] Nenhuma consulta para processar. Finalizando.');
                return Command::SUCCESS;
            }

            // ETAPA 2: PREPARAÇÃO DOS DADOS
            Log::info('🔧 [BACKUP] ETAPA 2: Preparando dados para o PDF...');
            
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

            // ETAPA 3: GERAÇÃO DO PDF
            Log::info('📄 [BACKUP] ETAPA 3: Gerando PDF...');
            
            try {
                $pdf = \PDF::loadView('pdf.backup_consultas', [
                    'dados'       => $dados,
                    'porEmpresa'  => $porEmpresa,
                    'porGR'       => $porGR,
                    'porStatus'   => $porStatus,
                    'porFilial'   => $porFilial,
                    'data'        => now()->format('d/m/Y H:i'),
                    'total'       => count($dados),
                ]);
                
                Log::info('✅ [BACKUP] PDF gerado com sucesso', [
                    'total_registros_pdf' => count($dados)
                ]);
            } catch (\Throwable $e) {
                Log::error('❌ [BACKUP] Erro ao gerar PDF', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'view' => 'pdf.backup_consultas'
                ]);
                throw $e;
            }

            // ETAPA 4: SALVANDO ARQUIVO
            Log::info('💾 [BACKUP] ETAPA 4: Salvando arquivo PDF...');
            
            $fileName = 'backup_consultas_' . now()->format('Y_m_d_His') . '.pdf';
            $filePath = storage_path("app/backups/{$fileName}");

            if (!is_dir(dirname($filePath))) {
                Log::info('📁 [BACKUP] Criando diretório: ' . dirname($filePath));
                mkdir(dirname($filePath), 0755, true);
            }

            try {
                $pdfContent = $pdf->output();
                $fileSize = strlen($pdfContent);
                file_put_contents($filePath, $pdfContent);
                
                Log::info('✅ [BACKUP] Arquivo salvo', [
                    'caminho' => $filePath,
                    'tamanho_bytes' => $fileSize,
                    'tamanho_mb' => round($fileSize / 1024 / 1024, 2)
                ]);
            } catch (\Throwable $e) {
                Log::error('❌ [BACKUP] Erro ao salvar arquivo', [
                    'error' => $e->getMessage(),
                    'path' => $filePath
                ]);
                throw $e;
            }

            // ETAPA 5: ENVIO DE EMAIL
            Log::info('📧 [BACKUP] ETAPA 5: Enviando emails...');
            $emailEnviado = $this->enviarEmailComAnexo($filePath, $fileName);
            
            if (!$emailEnviado) {
                Log::error('❌ [BACKUP] Falha no envio de emails. Abortando.');
                $this->error('Falha no envio de emails.');
                return Command::FAILURE;
            }

            // ETAPA 6: ENVIO PARA TELEGRAM
            Log::info('📱 [BACKUP] ETAPA 6: Enviando para Telegram...');
            $telegramEnviado = $this->enviarParaTelegram($filePath);
            
            if (!$telegramEnviado) {
                Log::error('❌ [BACKUP] Falha no envio para Telegram. Abortando.');
                $this->error('Falha no envio para Telegram.');
                return Command::FAILURE;
            }

            // ETAPA 7: LIMPEZA DO BANCO (apenas se ambos envios foram bem-sucedidos)
            Log::info('🗑️ [BACKUP] ETAPA 7: Limpando registros enviados...');
            
            $registrosAntes = DB::table('consultas')->where('status', 'ENVIADO')->count();
            
            DB::transaction(function () {
                DB::table('consultas')
                    ->where('status', 'ENVIADO')
                    ->delete();
            });
            
            $registrosDepois = DB::table('consultas')->where('status', 'ENVIADO')->count();
            $registrosDeletados = $registrosAntes - $registrosDepois;
            
            Log::info('✅ [BACKUP] Registros limpos', [
                'deletados' => $registrosDeletados,
                'restantes_enviado' => $registrosDepois
            ]);

            // ETAPA 8: FINALIZAÇÃO
            $executionTime = round(microtime(true) - $startTime, 2);
            
            Log::info('🎉 [BACKUP] ===== BACKUP CONCLUÍDO COM SUCESSO =====', [
                'tempo_execucao' => $executionTime . ' segundos',
                'arquivo_gerado' => $fileName,
                'tamanho_arquivo' => filesize($filePath),
                'emails_enviados' => $emailEnviado,
                'telegram_enviado' => $telegramEnviado,
                'registros_processados' => $totalConsultas,
                'registros_deletados' => $registrosDeletados,
                'memory_final' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
            ]);

            $this->info("✅ Backup concluído com sucesso em {$executionTime} segundos!");
            $this->info("📁 Arquivo: {$fileName}");
            $this->info("🗑️ Registros deletados: {$registrosDeletados}");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 2);
            
            Log::error('💥 [BACKUP] ===== ERRO CRÍTICO NO BACKUP =====', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'tempo_execucao' => $executionTime . ' segundos',
                'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
            ]);

            $this->error("❌ Erro no backup: " . $e->getMessage());
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
        $emailsComErro = [];
        $emailsEnviados = [];

        Log::info('📧 [EMAIL] Iniciando envio para ' . array_sum(array_map('count', $emailGrupos)) . ' destinatários');

        foreach ($emailGrupos as $grupo => $emails) {
            Log::info("📧 [EMAIL] Enviando para grupo: {$grupo}", [
                'destinatarios' => $emails
            ]);
            
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
                    
                    $emailsEnviados[] = $email;
                    Log::info("✅ [EMAIL] Enviado para: {$email}");
                    
                } catch (\Throwable $e) {
                    Log::error("❌ [EMAIL] Falha ao enviar para {$email}", [
                        'error' => $e->getMessage(),
                        'exception' => get_class($e)
                    ]);
                    $emailsComErro[] = $email;
                    $todosEnviados = false;
                }
            }
        }

        if ($todosEnviados) {
            Log::info('✅ [EMAIL] Todos os emails foram enviados com sucesso', [
                'total_enviados' => count($emailsEnviados)
            ]);
        } else {
            Log::error('❌ [EMAIL] Falha no envio de alguns emails', [
                'enviados' => $emailsEnviados,
                'com_erro' => $emailsComErro,
                'total_enviados' => count($emailsEnviados),
                'total_erros' => count($emailsComErro)
            ]);
        }

        return $todosEnviados;
    }

    private function enviarParaTelegram(string $filePath): bool
    {
        Log::info('📱 [TELEGRAM] Iniciando envio para Telegram');
        
        try {
            $botToken = config('services.telegram.bot_token');
            $chatId = config('services.telegram.chat_id');

            Log::debug('📱 [TELEGRAM] Configurações carregadas', [
                'bot_token_defined' => !empty($botToken),
                'bot_token_first_10_chars' => substr($botToken, 0, 10) . '...',
                'chat_id_defined' => !empty($chatId),
                'chat_id' => $chatId,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath),
                'is_readable' => is_readable($filePath),
                'file_size' => file_exists($filePath) ? filesize($filePath) : 0,
            ]);

            if (!$botToken) {
                Log::error('❌ [TELEGRAM] Token do bot não definido (TELEGRAM_BOT_TOKEN)');
                return false;
            }

            if (!$chatId) {
                Log::error('❌ [TELEGRAM] Chat ID não definido (TELEGRAM_CHAT_ID)');
                return false;
            }

            if (!file_exists($filePath)) {
                Log::error('❌ [TELEGRAM] Arquivo não encontrado', [
                    'path' => $filePath,
                    'realpath' => realpath($filePath),
                    'cwd' => getcwd(),
                    'storage_path' => storage_path(),
                ]);
                return false;
            }

            $fileSize = filesize($filePath);
            Log::info('📱 [TELEGRAM] Preparando envio', [
                'arquivo' => basename($filePath),
                'tamanho_bytes' => $fileSize,
                'tamanho_mb' => round($fileSize / 1024 / 1024, 2),
                'arquivo_real' => realpath($filePath)
            ]);

            // Verificar tamanho máximo do Telegram (50MB)
            if ($fileSize > 50 * 1024 * 1024) {
                Log::error('❌ [TELEGRAM] Arquivo muito grande para Telegram', [
                    'tamanho_mb' => round($fileSize / 1024 / 1024, 2),
                    'limite_mb' => 50
                ]);
                return false;
            }

            // Testar leitura do arquivo
            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                Log::error('❌ [TELEGRAM] Não foi possível ler o arquivo', [
                    'path' => $filePath,
                    'error' => error_get_last()
                ]);
                return false;
            }

            Log::info('📱 [TELEGRAM] Arquivo lido com sucesso', [
                'content_length' => strlen($fileContent)
            ]);

            Log::info('📱 [TELEGRAM] Enviando para API do Telegram...');
            
            $response = Http::timeout(120)
                ->withOptions([
                    'verify' => false, // IMPORTANTE: Para testes
                ])
                ->attach(
                    'document',
                    $fileContent,
                    basename($filePath),
                    ['Content-Type' => 'application/pdf']
                )
                ->post("https://api.telegram.org/bot{$botToken}/sendDocument", [
                    'chat_id' => $chatId,
                    'caption' => '📄 Backup diário das consultas - ' . now()->format('d/m/Y H:i:s'),
                    'parse_mode' => 'Markdown',
                ]);

            Log::info('📱 [TELEGRAM] Resposta completa', [
                'status_code' => $response->status(),
                'success' => $response->successful(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('✅ [TELEGRAM] Arquivo enviado com sucesso', [
                    'message_id' => $responseData['result']['message_id'] ?? 'N/A',
                    'file_id' => $responseData['result']['document']['file_id'] ?? 'N/A',
                    'file_name' => $responseData['result']['document']['file_name'] ?? 'N/A'
                ]);
                return true;
            } else {
                $errorData = $response->json();
                Log::error('❌ [TELEGRAM] Falha na API do Telegram', [
                    'status_code' => $response->status(),
                    'error_code' => $errorData['error_code'] ?? 'N/A',
                    'description' => $errorData['description'] ?? 'N/A',
                    'response_body' => $response->body(),
                    'full_response' => $response
                ]);
                
                // Tentar enviar uma mensagem de erro para o Telegram (para debug)
                try {
                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => "❌ Erro ao enviar backup: " . ($errorData['description'] ?? 'Erro desconhecido'),
                        'parse_mode' => 'Markdown'
                    ]);
                } catch (\Throwable $e) {
                    // Ignorar erro secundário
                }
                
                return false;
            }

        } catch (\Throwable $e) {
            Log::error('💥 [TELEGRAM] Erro de exceção ao enviar', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}