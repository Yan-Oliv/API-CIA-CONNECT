<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HttpResponses;

// 1|p3gD0f2nldobeWPbpEbrgJXYEjBHDb1tlsQa0fP46b3cd1c6

class AuthController extends Controller
{

    use HttpResponses;

    public function login(Request $request)
    {
        
        if (Auth::attempt($request->only('email', 'password'))){
            return response()->json(['message' => 'Authorization!', 'status' => 200, 'token' => $request->user()->createToken('ciacat')->plainTextToken]);
        }
        return response()->json(['message' => 'Not Authorization!', 'status' => 403]);
    }

    public function logout(Request $request)
    {
        \Log::info('Usuário:', ['user' => $request->user()]);

        $user = $request->user();
        if($user){
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Token Revoked', 'status' => 200]);
        }
        return response()->json(['message' => 'Token not Revoked!', 'status' => 403]);
    }

    public function validateToken(Request $request)
    {
        $user = $request->user(); // Obtém o usuário autenticado através do token

        if ($user) {
            return response()->json([
                'message' => 'Token is valid',
                'status' => 200,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);
        }

        return response()->json(['message' => 'Invalid token', 'status' => 401]);
    }

}
