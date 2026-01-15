<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    /**
     * LOGIN
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

            // apaga tokens antigos (opcional, mas recomendável)
            $user->tokens()->delete();

            $token = $user->createToken('ciacat')->plainTextToken;

            return response()->json([
                'token' => $token,
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
            ]);

            return response()->json([
                'message' => 'Erro interno ao autenticar',
            ], 500);
        }
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                $user->currentAccessToken()?->delete();

                Log::info('[AUTH] Logout realizado', [
                    'user_id' => $user->id,
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
     * VALIDA TOKEN
     */
    public function validateToken(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Token inválido',
                ], 401);
            }

            return response()->json([
                'message' => 'Token válido',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ], 200);

        } catch (Throwable $e) {
            Log::error('[AUTH] Erro ao validar token', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao validar token',
            ], 500);
        }
    }
}