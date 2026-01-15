<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\BaseApiController;
use App\Models\Referencias\Requisitos;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class RequisitosController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $data = Requisitos::orderByDesc('last_update')->get();
            return $this->success($data);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao listar requisitos');
        }
    }

    public function filter(int $id)
    {
        $requisito = Requisitos::find($id);

        if (!$requisito) {
            return $this->error('Requisito não encontrado', 404);
        }

        return $this->success($requisito);
    }

    public function cad(Request $request)
    {
        try {
            $validated = $request->validate([
                'requisitos' => 'required|string|max:255',
                'user_id'    => 'required|integer|exists:users,id',
            ]);

            $requisito = Requisitos::create($validated);

            return $this->success($requisito, 201);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao cadastrar requisito', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function edit(Request $request, int $id)
    {
        $requisito = Requisitos::find($id);

        if (!$requisito) {
            return $this->error('Requisito não encontrado', 404);
        }

        try {
            $validated = $request->validate([
                'requisitos' => 'required|string|max:255',
            ]);

            $requisito->update($validated);

            return $this->success($requisito);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao atualizar requisito', [
                'id' => $id,
            ]);
        }
    }

    public function delete(int $id)
    {
        $requisito = Requisitos::find($id);

        if (!$requisito) {
            return $this->error('Requisito não encontrado', 404);
        }

        try {
            $requisito->delete();
            return $this->success(['message' => 'Excluído com sucesso']);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao excluir requisito', [
                'id' => $id,
            ]);
        }
    }

    public function filterRequisitos(Request $request)
    {
        try {
            $ids = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer',
            ])['ids'];

            $data = Requisitos::whereIn('id', $ids)->get();

            return $this->success($data);

        } catch (ValidationException $e) {
            return $this->error(
                'IDs inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao filtrar requisitos');
        }
    }
}