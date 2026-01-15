<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\BaseApiController;
use App\Models\Funcionalidades\Rastreio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class RastreioController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $data = Rastreio::orderByDesc('last_update')->get();
            return $this->success($data);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao listar rastreios');
        }
    }

    public function filter(int $id)
    {
        $rastreio = Rastreio::find($id);

        if (!$rastreio) {
            return $this->error('Rastreio não encontrado', 404, ['id' => $id]);
        }

        return $this->success($rastreio);
    }

    public function cad(Request $request)
    {
        try {
            $validated = $this->validateData($request);
            $rastreio = Rastreio::create($validated);

            Log::info('[RASTREIO CREATED]', [
                'id' => $rastreio->id,
                'user_id' => $request->user()->id ?? null
            ]);

            return $this->success($rastreio, 201);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao criar rastreio', [
                'payload' => $request->all()
            ]);
        }
    }

    public function edit(Request $request, int $id)
    {
        $rastreio = Rastreio::find($id);

        if (!$rastreio) {
            return $this->error('Rastreio não encontrado', 404, ['id' => $id]);
        }

        try {
            $validated = $this->validateData($request);
            $rastreio->update($validated);

            Log::info('[RASTREIO UPDATED]', [
                'id' => $id,
                'user_id' => $request->user()->id ?? null
            ]);

            return $this->success($rastreio);

        } catch (ValidationException $e) {
            return $this->error('Dados inválidos', 422, [
                'errors' => $e->errors(),
                'id' => $id
            ]);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao atualizar rastreio', [
                'id' => $id,
                'payload' => $request->all()
            ]);
        }
    }

    public function delete(int $id)
    {
        $rastreio = Rastreio::find($id);

        if (!$rastreio) {
            return $this->error('Rastreio não encontrado', 404, ['id' => $id]);
        }

        try {
            $rastreio->delete();

            Log::warning('[RASTREIO DELETED]', [
                'id' => $id
            ]);

            return $this->success(['message' => 'Excluído com sucesso']);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao excluir rastreio', ['id' => $id]);
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'dt_id'             => 'nullable|string|max:255',
            'motorista'         => 'nullable|string|max:255',
            'cpf'               => 'nullable|string|max:255',
            'placa_cavalo'      => 'nullable|string|max:255',
            'placa_reboque'     => 'nullable|string|max:255',
            'tipo_rastreador'   => 'nullable|string|max:255',
            'status_rastreador' => 'nullable|string|max:255',
            'buonny'            => 'nullable|string|max:255',
            'brk'               => 'nullable|string|max:255',
            'check_list'        => 'nullable|string|max:255',
            'id_rastreador'     => 'nullable|string|max:255',
            'login_rast'        => 'nullable|string|max:255',
            'pass_rast'         => 'nullable|string|max:255',
            'status'            => 'nullable|string|max:255',
            'user_id'           => 'required|integer|exists:users,id',
        ]);
    }
}