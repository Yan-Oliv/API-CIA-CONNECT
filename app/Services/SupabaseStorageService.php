<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseStorageService
{
    protected string $url;
    protected string $key;
    protected string $bucket;

    public function __construct()
    {
        $this->url = rtrim(env('SUPABASE_URL'), '/');
        $this->key = env('SUPABASE_SERVICE_ROLE_KEY');
        $this->bucket = env('SUPABASE_BUCKET', 'avatars');
    }

    public function uploadAvatar(int $userId, string $base64, string $extension): string
    {
        $fileName = "{$userId}_profile.{$extension}";
        $path = "avatars/{$fileName}"; // caminho dentro do bucket

        $binary = base64_decode($base64);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->key}",
            'Content-Type'  => $this->getMimeType($extension),
        ])->withBody(
            $binary,
            $this->getMimeType($extension)
        )->put(
            "{$this->url}/storage/v1/object/{$this->bucket}/{$fileName}"
        );

        if (!$response->successful()) {
            throw new \Exception('Erro ao enviar imagem para o Supabase Storage: ' . $response->body());
        }

        return $path; // salva só o caminho relativo no DB
    }

    public function getPublicUrl(string $filePath): string
    {
        return "{$this->url}/storage/v1/object/public/{$filePath}";
    }

    private function getMimeType(string $ext): string
    {
        return match (strtolower($ext)) {
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            default => 'application/octet-stream',
        };
    }
}
