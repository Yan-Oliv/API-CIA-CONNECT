<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function enviarBackup(Request $request)
    {
        try {
            // Verifica se o arquivo PDF foi enviado
            if (!$request->hasFile('pdf') || !$request->file('pdf')->isValid()) {
                return response()->json(['error' => 'Arquivo PDF inválido ou ausente.'], 400);
            }

            $pdf = $request->file('pdf');
            $fileName = 'backup_consultas_' . now()->format('Y_m_d_His') . '.pdf';
            $filePath = $pdf->storeAs('', $fileName); // Armazena temporariamente

            $fullPath = storage_path("app/$filePath");

            // Envia por e-mail
            $this->enviarEmailComAnexo($fullPath, $fileName);

            // (Opcional) Envia para Telegram
            $this->enviarParaTelegram($fullPath);

            return response()->json(['message' => 'Backup enviado com sucesso.']);
        } catch (\Exception $e) {
            Log::error('[BackupController] Erro ao enviar backup: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno ao enviar o backup.'], 500);
        }
    }

    private function enviarEmailComAnexo($filePath, $fileName)
    {
        $emailGrupos = [
            'Gerentes' => ["eder.catalao@hotmail.com"],
            'Gestores' => ["eder.henrique@ciacargas.com.br"],
            'Operacional' => [
                "filialgo@ciacargas.com.br",
                "ciacargasgo2@ciacargas.com.br",
            ],
        ];

        foreach ($emailGrupos as $grupo => $emails) {
            foreach ($emails as $email) {
                Mail::send([], [], function ($message) use ($email, $filePath, $fileName, $grupo) {
                    $message->to($email)
                        ->subject('⚠️🚨 BACKUP DIÁRIO CONSULTAS - CIA CATALÃO 🚨⚠️')
                        ->setBody("Grupo: $grupo\nSegue em anexo o backup das consultas do dia.", 'text/plain')
                        ->attach($filePath, [
                            'as' => $fileName,
                            'mime' => 'application/pdf',
                        ]);
                });
            }
        }
    }

    private function enviarParaTelegram($filePath)
    {
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('TELEGRAM_CHAT_ID');

            if (!$botToken || !$chatId) {
                Log::warning('[Telegram] Token ou Chat ID não definidos.');
                return;
            }

            $response = Http::attach(
                'document', file_get_contents($filePath), basename($filePath)
            )->post("https://api.telegram.org/bot{$botToken}/sendDocument", [
                'chat_id' => $chatId,
                'caption' => '📄 Backup diário das consultas - ' . now()->format('d/m/Y H:i'),
            ]);

            if (!$response->successful()) {
                Log::warning('[Telegram] Falha ao enviar: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('[Telegram] Erro ao enviar: ' . $e->getMessage());
        }
    }
}
