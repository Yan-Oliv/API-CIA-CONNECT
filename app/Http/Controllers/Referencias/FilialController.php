<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\BaseApiController;
use App\Models\Referencias\Filial;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class FilialController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            return $this->success(
                Filial::orderByDesc('last_update')->get()
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao listar filiais');
        }
    }

    public function filter(int $id)
    {
        $filial = Filial::find($id);

        if (!$filial) {
            return $this->error('Filial não encontrada', 404);
        }

        return $this->success($filial);
    }

    public function cad(Request $request)
    {
        try {
            $data = $request->validate([
                'filial'  => 'required|string|max:255',
                'estado'  => 'required|string|max:255',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $filial = Filial::create($data);

            return $this->success($filial, 201);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao cadastrar filial', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function edit(Request $request, int $id)
    {
        $filial = Filial::find($id);

        if (!$filial) {
            return $this->error('Filial não encontrada', 404);
        }

        try {
            $data = $request->validate([
                'filial' => 'required|string|max:255',
                'estado' => 'required|string|max:255',
            ]);

            $filial->update($data);

            return $this->success($filial);

        } catch (ValidationException $e) {
            return $this->error('Dados inválidos', 422, [
                'errors' => $e->errors()
            ]);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao atualizar filial', [
                'filial_id' => $id,
            ]);
        }
    }

    public function delete(int $id)
    {
        $filial = Filial::find($id);

        if (!$filial) {
            return $this->error('Filial não encontrada', 404);
        }

        try {
            $filial->delete();

            return $this->success(['message' => 'Filial excluída com sucesso']);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao excluir filial', [
                'filial_id' => $id,
            ]);
        }
    }
}