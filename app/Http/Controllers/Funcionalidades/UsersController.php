<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\BaseApiController;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class UsersController extends BaseApiController
{
    private SupabaseStorageService $storage;

    public function __construct(SupabaseStorageService $storage)
    {
        $this->storage = $storage;
    }

    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $users = User::all();

            foreach ($users as $user) {
                if ($user->perfil) {
                    $user->perfil = $this->storage->getPublicUrl($user->perfil);
                }
            }

            return $this->success($users);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao listar usuários');
        }
    }

    public function filter(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('Usuário não encontrado', 404, ['id' => $id]);
        }

        if ($user->perfil) {
            $user->perfil = $this->storage->getPublicUrl($user->perfil);
        }

        return $this->success($user);
    }

    public function cad(Request $request)
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255',
                'email'       => 'required|string|email|unique:users,email',
                'telefone'    => 'nullable|string|max:20',
                'password'    => 'required|string|min:8',
                'role'        => 'required|string',
                'filial_id'   => 'nullable|integer',
                'first_login' => 'boolean',
                'perfil'      => 'nullable|string', // Adicionar perfil como opcional
            ]);

            $data['password'] = Hash::make($data['password']);
            $data['perfil']   = $data['perfil'] ?? 'avatars/default_profile.png';
            
            // Converte first_login para booleano se necessário
            if (isset($data['first_login'])) {
                $data['first_login'] = filter_var($data['first_login'], FILTER_VALIDATE_BOOLEAN);
            } else {
                $data['first_login'] = true; // Valor padrão
            }

            $user = User::create($data);

            Log::info('[USER CREATED]', [
                'id' => $user->id,
                'email' => $user->email,
                'filial_id' => $user->filial_id,
            ]);

            return $this->success($user, 201);

        } catch (ValidationException $e) {
            Log::error('Validation error in cad:', ['errors' => $e->errors()]);
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            Log::error('Error creating user:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);
            
            return $this->exception($e, 'Erro ao criar usuário', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function edit(Request $request, int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('Usuário não encontrado', 404, ['id' => $id]);
        }

        try {
            $data = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|string|email|unique:users,email,' . $id,
                'telefone'  => 'nullable|string|max:20',
                'password'  => 'nullable|string|min:8',
                'role'      => 'required|string',
                'filial_id' => 'nullable|integer',
                'perfil'    => 'nullable|string',
            ]);

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            if ($request->filled('perfil')) {
                preg_match('/data:image\/(\w+);base64,/', $request->perfil, $matches);
                $ext = $matches[1] ?? 'jpg';

                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->perfil);
                $data['perfil'] = $this->storage->uploadAvatar(
                    $user->id,
                    $base64,
                    $ext
                );
            }

            $user->update($data);

            Log::info('[USER UPDATED]', ['id' => $id]);

            return $this->success($user);

        } catch (ValidationException $e) {
            return $this->error('Dados inválidos', 422, [
                'errors' => $e->errors(),
                'id' => $id,
            ]);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao atualizar usuário', [
                'id' => $id,
                'payload' => $request->all(),
            ]);
        }
    }

    public function delete(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('Usuário não encontrado', 404, ['id' => $id]);
        }

        try {
            $user->delete();

            Log::warning('[USER DELETED]', ['id' => $id]);

            return $this->success(['message' => 'Usuário excluído com sucesso']);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao excluir usuário', ['id' => $id]);
        }
    }
}