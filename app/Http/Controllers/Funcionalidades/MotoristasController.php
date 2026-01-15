<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\BaseApiController;
use App\Models\Funcionalidades\Motoristas;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Throwable;

class MotoristasController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $motoristas = Motoristas::orderByDesc('last_update')->get();
            return $this->success($motoristas);
        } catch (Throwable $e) {
            return $this->exception($e, '[MOTORISTAS] Erro ao listar');
        }
    }

    public function filter(int $id)
    {
        try {
            $motorista = Motoristas::find($id);

            if (!$motorista) {
                return $this->error('Motorista não encontrado', 404);
            }

            return $this->success($motorista);
        } catch (Throwable $e) {
            return $this->exception($e, '[MOTORISTAS] Erro ao buscar', ['id' => $id]);
        }
    }

    public function cad(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validate([
                'nome' => 'required|string|max:255',
                'telefone' => 'required|string|max:20',
                'vei_id' => 'required|integer|exists:veiculos,id',
                'user_id' => 'required|integer|exists:users,id',
                'car_id' => 'nullable|integer|exists:carrocerias,id',
                'quantidade_paletes' => 'nullable|integer',
                'peso' => 'nullable|numeric',
                'metragem_cubica' => 'nullable|numeric',
                'cpf' => 'nullable|string|max:20|unique:motoristas,cpf',
                'observacao' => 'nullable|string',
            ]);

            $motorista = Motoristas::create($data);

            DB::commit();
            return $this->success($motorista, 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();
            return $this->exception($e, '[MOTORISTAS] Erro ao criar', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function edit(Request $request, int $id)
    {
        DB::beginTransaction();

        try {
            $motorista = Motoristas::find($id);

            if (!$motorista) {
                return $this->error('Motorista não encontrado', 404);
            }

            $data = $request->validate([
                'nome' => 'required|string|max:255',
                'telefone' => 'required|string|max:20',
                'vei_id' => 'required|integer|exists:veiculos,id',
                'cpf' => 'nullable|string|max:20|unique:motoristas,cpf,' . $id,
                'observacao' => 'nullable|string',
            ]);

            $motorista->update($data);

            DB::commit();
            return $this->success($motorista);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();
            return $this->exception($e, '[MOTORISTAS] Erro ao atualizar', [
                'id' => $id,
                'payload' => $request->all(),
            ]);
        }
    }

    public function delete(int $id)
    {
        try {
            $motorista = Motoristas::find($id);

            if (!$motorista) {
                return $this->error('Motorista não encontrado', 404);
            }

            $motorista->delete();
            return $this->success(['message' => 'Motorista excluído']);

        } catch (Throwable $e) {
            return $this->exception($e, '[MOTORISTAS] Erro ao excluir', ['id' => $id]);
        }
    }
}