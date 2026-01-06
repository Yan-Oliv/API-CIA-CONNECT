<?php

namespace App\Http\Controllers\Funcionalidades;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionalidades\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\SupabaseStorageService;



class UsersController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => "OK",
        ]);
    }

    public function search()
    {
        $users = Users::all();

        foreach ($users as $user) {
            if ($user->perfil) {
                $user->perfil = $user->perfil
                    ? env('SUPABASE_URL') . '/storage/v1/object/public/' . $user->perfil
                    : null;
            }
        }
        
        return response()->json($users, 200);
    }

    public function listEmail(Request $request)
    {
        // Valida se o e-mail foi enviado
        $validated = $request->validate([
            'email' => 'required|string|email'
        ]);
    
        // Busca o usuário pelo e-mail
        $user = Users::where('email', $validated['email'])->first();
    
        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }
    
        return response()->json([
            'id' => (int) $user->id,
            'email' => $user->email,
        ], 200);
    }

    public function login(Request $requisitar)
    {
        $valide = $requisitar->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = Users::where('email', $valide['email'])->first();

        if (!$user || !Hash::check($valide['password'], $user->password)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        // Evita retornar informações sensíveis, como a senha
        $userData = $user->only(['id', 'name', 'email', 'telefone', 'role', 'filial_id', 'first_login']);

        if ($user->perfil) {
            $storage = new SupabaseStorageService();
            $user->perfil = $storage->getPublicUrl($user->perfil);
        }


        return response()->json([
            'status' => 'OK',
            'user' => $userData,
        ], 200);
    }

    public function filter($id)
    {
        $user = Users::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        // Se houver imagem, converte para base64 com prefixo do tipo
        if ($user->perfil) {
            $storage = new SupabaseStorageService();
            $user->perfil = $storage->getPublicUrl($user->perfil);
        }

        return response()->json($user, 200);
    }

    public function cad(Request $requisitar)
    {
        $valide = $requisitar->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|max:255|unique:users,email',
            'telefone'    => 'nullable|string|max:20',
            'password'    => 'required|string|max:255',
            'role'        => 'required|string|max:255',
            'filial_id'   => 'nullable|integer',
            'first_login' => 'boolean',
        ]);

        $valide['perfil'] = 'avatars/default_profile.png';
        $valide['password'] = Hash::make($valide['password']);

        $user = Users::create($valide);

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'user' => $user->only(['id', 'name', 'email', 'role', 'filial_id']),
        ], 201);
    }

    public function edit(Request $requisitar, $id)
    {
        $user = Users::find($id);
        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $valide = $requisitar->validate([
            'perfil'    => 'nullable|string',
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|max:255|unique:users,email,' . $id,
            'telefone'  => 'nullable|string|max:20',
            'password'  => 'nullable|string|max:255',
            'role'      => 'required|string|max:255',
            'filial_id' => 'nullable|integer',
        ]);

        if (!empty($valide['password'])) {
            $valide['password'] = Hash::make($valide['password']);
        } else {
            unset($valide['password']);
        }

        if ($requisitar->filled('perfil')) {
            preg_match('/data:image\/(\w+);base64,/', $requisitar->perfil, $matches);
            $extension = $matches[1] ?? 'jpg';

            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $requisitar->perfil);

            $storage = new SupabaseStorageService();

            $path = $storage->uploadAvatar(
                $user->id,
                $base64,
                $extension
            );

            $valide['perfil'] = $path;
        }

        $user->update($valide);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'user' => $user->only(['id', 'name', 'email', 'role', 'filial_id']),
        ], 200);
    }


    public function changePassword(Request $requisitar, $id)
    {
        // Buscar o usuário
        $user = Users::find($id);
    
        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }
    
        // Validar os campos
        $valide = $requisitar->validate([
            'current_password' => 'required|string', // Senha atual (se necessário validar)
            'new_password' => 'required|string|min:8|max:255|different:current_password', // Nova senha
            'confirm_password' => 'required|string|same:new_password', // Confirmar nova senha
        ]);
    
        // Verificar a senha atual
        if (!Hash::check($valide['current_password'], $user->password)) {
            return response()->json(['error' => 'Senha atual está incorreta'], 403);
        }
    
        try {
            // Atualizar a senha
            $user->password = Hash::make($valide['new_password']);
            $user->save();
    
            return response()->json(['message' => 'Senha alterada com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao alterar senha: ' . $e->getMessage()], 500);
        }
    }

    public function resetPasswordByEmail(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'email' => 'required|string|email',
            'new_password' => 'required|string|min:8|max:255',
            'confirm_password' => 'required|string|same:new_password',
        ]);
    
        // Buscar o usuário pelo e-mail
        $user = Users::where('email', $validated['email'])->first();
    
        if (!$user) {
            return response()->json(['error' => 'Usuário com este e-mail não foi encontrado'], 404);
        }
    
        // Verifica se a nova senha é igual à atual
        if (Hash::check($validated['new_password'], $user->password)) {
            return response()->json(['error' => 'A nova senha não pode ser igual à atual.'], 400);
        }
    
        try {
            // Atualizar a senha
            $user->password = Hash::make($validated['new_password']);
            $user->save();
    
            return response()->json(['message' => 'Senha redefinida com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao redefinir senha: ' . $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $user = Users::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Usuário excluído com sucesso'], 200);
    }
}
