<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Throwable;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    // Constantes
    private const MAX_SESSIONS = 3;
    private const TOKEN_EXPIRY_HOURS = 48;

    /**
     * LOGIN com gerenciamento de múltiplas sessões
     */
    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'message' => 'E-mail ou senha inválidos',
                ], 403);
            }

            // Verificar número de sessões ativas
            $activeTokens = $user->tokens()
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->count();

            if ($activeTokens >= self::MAX_SESSIONS) {
                return response()->json([
                    'message' => 'Limite de sessões atingido (máximo 3 dispositivos)',
                    'code' => 'SESSION_LIMIT_EXCEEDED'
                ], 403);
            }

            // Gerar ID único do dispositivo
            $deviceId = $request->header('X-Device-Id', Str::uuid()->toString());
            $rawDeviceName = $request->header('User-Agent', 'Dispositivo Desconhecido');
            
            // 🔧 **MELHORIA: Formatar device_name para algo mais amigável**
            $deviceName = $this->formatDeviceName($rawDeviceName);
            $ipAddress = $request->ip();

            // 🔧 **CORREÇÃO DO FUSO HORÁRIO**: Forçar fuso de São Paulo
            $expiresAt = Carbon::now('America/Sao_Paulo')->addHours(self::TOKEN_EXPIRY_HOURS);

            // 🔧 **SOLUÇÃO DEFINITIVA**: Usar transação e salvar manualmente
            DB::beginTransaction();
            
            try {
                // Criar token
                $token = $user->createToken('ciacat', ['*'], $expiresAt);
                
                // Obter o ID do token recém-criado
                $tokenId = $token->accessToken->id;
                
                // Atualizar com os dados adicionais
                \Laravel\Sanctum\PersonalAccessToken::where('id', $tokenId)->update([
                    'device_id' => $deviceId,
                    'device_name' => $deviceName, // ← Já formatado
                    'ip_address' => $ipAddress,
                    'expires_at' => $expiresAt,
                ]);
                
                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            Log::info('[AUTH] Novo login', [
                'user_id' => $user->id,
                'device_id' => $deviceId,
                'device_name' => $deviceName,
                'ip_address' => $ipAddress,
                'token_id' => $tokenId,
                'expires_at' => $expiresAt->toISOString(),
                'timezone' => config('app.timezone'),
            ]);

            return response()->json([
                'token' => $token->plainTextToken,
                'device_id' => $deviceId,
                'expires_at' => $expiresAt->toISOString(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'filial_id' => $user->filial_id,
                ],
            ], 200);

        } catch (Throwable $e) {
            Log::error('[AUTH] Erro no login', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erro interno ao autenticar',
            ], 500);
        }
    }

    /**
     * Formata o nome do dispositivo para algo mais amigável
     */
    private function formatDeviceName(string $userAgent): string
    {
        // Navegadores
        if (strpos($userAgent, 'Chrome') !== false) {
            if (strpos($userAgent, 'Mobile') !== false) {
                return 'Chrome Mobile';
            }
            return 'Google Chrome';
        }
        
        if (strpos($userAgent, 'Firefox') !== false) {
            return 'Mozilla Firefox';
        }
        
        if (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
            if (strpos($userAgent, 'Mobile') !== false) {
                return 'Safari Mobile';
            }
            return 'Safari';
        }
        
        if (strpos($userAgent, 'Edge') !== false) {
            return 'Microsoft Edge';
        }
        
        if (strpos($userAgent, 'Opera') !== false) {
            return 'Opera';
        }
        
        // Sistemas operacionais
        if (strpos($userAgent, 'Windows') !== false) {
            if (strpos($userAgent, 'Windows NT 10.0') !== false) {
                return 'Windows 10';
            }
            if (strpos($userAgent, 'Windows NT 6.3') !== false) {
                return 'Windows 8.1';
            }
            if (strpos($userAgent, 'Windows NT 6.2') !== false) {
                return 'Windows 8';
            }
            if (strpos($userAgent, 'Windows NT 6.1') !== false) {
                return 'Windows 7';
            }
            return 'Windows';
        }
        
        if (strpos($userAgent, 'Mac OS X') !== false) {
            if (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
                return 'iOS';
            }
            return 'macOS';
        }
        
        if (strpos($userAgent, 'Android') !== false) {
            return 'Android';
        }
        
        if (strpos($userAgent, 'Linux') !== false) {
            return 'Linux';
        }
        
        // Se não conseguir identificar, retorna algo genérico
        if (strlen($userAgent) > 50) {
            return substr($userAgent, 0, 50) . '...';
        }
        
        return $userAgent;
    }

    /**
     * LOGOUT por dispositivo
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $deviceId = $request->header('X-Device-Id');

            if ($user && $deviceId) {
                // Remove apenas o token do dispositivo atual
                $user->tokens()
                    ->where('device_id', $deviceId)
                    ->delete();

                Log::info('[AUTH] Logout por dispositivo', [
                    'user_id' => $user->id,
                    'device_id' => $deviceId,
                ]);
            }

            return response()->json([
                'message' => 'Logout realizado',
            ], 200);

        } catch (Throwable $e) {
            Log::error('[AUTH] Erro no logout', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao realizar logout',
            ], 500);
        }
    }

    /**
     * VALIDA TOKEN com verificação de expiração - VERSÃO TOLERANTE
     */
    public function validateToken(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Token inválido',
                    'valid' => false
                ], 401);
            }

            $deviceId = $request->header('X-Device-Id');
            
            // Se tiver deviceId, procura pelo token específico
            if ($deviceId) {
                // Buscar token específico do dispositivo
                $token = $user->tokens()
                    ->where('device_id', $deviceId)
                    ->first();

                if (!$token) {
                    // Token não encontrado para este dispositivo
                    return response()->json([
                        'message' => 'Token não encontrado para este dispositivo',
                        'valid' => false
                    ], 404);
                }

                // Verificar se o token expirou
                if ($token->expires_at && $token->expires_at->isPast()) {
                    $token->delete();
                    return response()->json([
                        'message' => 'Token expirado',
                        'valid' => false,
                        'expired' => true
                    ], 401);
                }

                Log::info('[AUTH] Token validado', [
                    'user_id' => $user->id,
                    'device_id' => $deviceId,
                    'token_id' => $token->id ?? null,
                    'expires_at' => $token->expires_at ?? null,
                ]);

                // Atualizar last_used_at
                $token->update(['last_used_at' => now()]);
                
                return response()->json([
                    'message' => 'Token válido',
                    'valid' => true,
                    'expires_at' => $token->expires_at?->toISOString(),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ], 200);
            } else {
                // Se não tem deviceId, valida apenas se o usuário está autenticado
                // (compatibilidade com tokens antigos)
                return response()->json([
                    'message' => 'Token válido (modo compatibilidade)',
                    'valid' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ], 200);
            }

        } catch (Throwable $e) {
            Log::error('[AUTH] Erro ao validar token', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erro ao validar token: ' . $e->getMessage(),
                'valid' => false
            ], 500);
        }
    }

    /**
     * LISTAR SESSÕES ATIVAS
     */
    public function listSessions(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([], 401);
            }

            $sessions = $user->tokens()
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->select(['id', 'device_id', 'device_name', 'ip_address', 
                         'last_used_at', 'expires_at', 'created_at'])
                ->get()
                ->map(function ($token) {
                    return [
                        'device_id' => $token->device_id,
                        'device_name' => $token->device_name,
                        'ip_address' => $token->ip_address,
                        'last_used' => $token->last_used_at?->diffForHumans(),
                        'expires_at' => $token->expires_at?->toISOString(),
                        'is_current' => $token->device_id === request()->header('X-Device-Id'),
                    ];
                });

            return response()->json([
                'sessions' => $sessions,
                'max_sessions' => self::MAX_SESSIONS,
            ], 200);

        } catch (Throwable $e) {
            Log::error('[AUTH] Erro ao listar sessões', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao listar sessões',
            ], 500);
        }
    }

    /**
     * REMOVER SESSÃO ESPECÍFICA
     */
    public function revokeSession(Request $request, $deviceId)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([], 401);
            }

            $deleted = $user->tokens()
                ->where('device_id', $deviceId)
                ->delete();

            if ($deleted) {
                Log::info('[AUTH] Sessão removida', [
                    'user_id' => $user->id,
                    'device_id' => $deviceId,
                    'revoked_by' => $request->header('X-Device-Id'),
                ]);
            }

            return response()->json([
                'message' => 'Sessão removida com sucesso',
                'removed' => $deleted > 0,
            ], 200);

        } catch (Throwable $e) {
            Log::error('[AUTH] Erro ao remover sessão', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao remover sessão',
            ], 500);
        }
    }
}