<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\BaseApiController;
use App\Models\Referencias\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class VeiculosController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $data = Veiculo::orderBy('nome')->get();
            return $this->success($data);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao listar veículos');
        }
    }

    public function filter(int $id)
    {
        $veiculo = Veiculo::find($id);

        if (!$veiculo) {
            return $this->error('Veículo não encontrado', 404);
        }

        return $this->success($veiculo);
    }

    public function cad(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome'    => 'required|string|max:255',
                'tipo'    => 'required|string|max:255',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $veiculo = Veiculo::create($validated);

            return $this->success($veiculo, 201);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao cadastrar veículo', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function edit(Request $request, int $id)
    {
        $veiculo = Veiculo::find($id);

        if (!$veiculo) {
            return $this->error('Veículo não encontrado', 404);
        }

        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'tipo' => 'required|string|max:255',
            ]);

            $veiculo->update($validated);

            return $this->success($veiculo);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao atualizar veículo', [
                'id' => $id,
            ]);
        }
    }

    public function delete(int $id)
    {
        $veiculo = Veiculo::find($id);

        if (!$veiculo) {
            return $this->error('Veículo não encontrado', 404);
        }

        try {
            $veiculo->delete();

            return $this->success(['message' => 'Veículo excluído com sucesso']);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao excluir veículo', [
                'id' => $id,
            ]);
        }
    }

    public function filterVeiculos(Request $request)
    {
        try {
            $ids = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer',
            ])['ids'];

            $data = Veiculo::whereIn('id', $ids)->get();

            return $this->success($data);

        } catch (ValidationException $e) {
            return $this->error(
                'IDs inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao filtrar veículos');
        }
    }
}