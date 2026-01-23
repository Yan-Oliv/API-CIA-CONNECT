<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseStorageService
{
    protected ?string $url;
    protected ?string $key;
    protected string $bucket;

    public function __construct()
    {
        // Tente config() primeiro, depois env(), com fallback
        $this->url = rtrim(
            config('services.supabase.url', 
            env('SUPABASE_URL', '')), 
            '/'
        );
        
        $this->key = config('services.supabase.service_role_key', 
            env('SUPABASE_SERVICE_ROLE_KEY', null));
            
        $this->bucket = config('services.supabase.bucket', 
            env('SUPABASE_BUCKET', 'avatars'));

        // Se ainda for null, defina como string vazia
        $this->key = $this->key ?? '';
        $this->url = $this->url ?? '';
    }

    public function uploadAvatar(int $userId, string $base64, string $extension): string
    {
        $fileName = "{$userId}_profile.{$extension}";
        $path = "avatars/{$fileName}";

        // Se não houver chave, apenas retorne o path sem fazer upload
        if (empty($this->key)) {
            Log::warning('SupabaseStorageService: Chave não configurada, skipando upload');
            return $path;
        }

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

        return $path;
    }

    public function getPublicUrl(string $filePath): string
    {
        if (empty($this->url)) {
            return "https://via.placeholder.com/150";
        }
        
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