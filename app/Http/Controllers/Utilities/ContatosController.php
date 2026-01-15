<?php

namespace App\Http\Controllers\Utilities;

use App\Http\Controllers\BaseApiController;
use App\Models\Utilities\Contatos;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContatosController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $contatos = Contatos::orderByDesc('last_update')->get();
            return $this->success($contatos);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao listar contatos');
        }
    }

    public function filter(int $id)
    {
        $contato = Contatos::find($id);

        if (!$contato) {
            return $this->error('Contato não encontrado', 404);
        }

        return $this->success($contato);
    }

    public function cad(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'   => 'required|string|max:255',
                'desc'    => 'nullable|string|max:255',
                'numero'  => 'nullable|string|max:50',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $contato = Contatos::create($validated);

            return $this->success($contato, 201);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao cadastrar contato', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function edit(Request $request, int $id)
    {
        $contato = Contatos::find($id);

        if (!$contato) {
            return $this->error('Contato não encontrado', 404);
        }

        try {
            $validated = $request->validate([
                'title'   => 'required|string|max:255',
                'desc'    => 'nullable|string|max:255',
                'numero'  => 'nullable|string|max:50',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $contato->update($validated);

            return $this->success($contato);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao atualizar contato', [
                'id' => $id,
            ]);
        }
    }

    public function delete(int $id)
    {
        $contato = Contatos::find($id);

        if (!$contato) {
            return $this->error('Contato não encontrado', 404);
        }

        try {
            $contato->delete();

            return $this->success([
                'message' => 'Contato excluído com sucesso'
            ]);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao excluir contato', [
                'id' => $id,
            ]);
        }
    }

    /**
     * ⚠️ EXTREMO CUIDADO
     * Ideal bloquear por ambiente ou role
     */
    public function destroy()
    {
        try {
            Contatos::truncate();

            return $this->success([
                'message' => 'Todos os contatos foram excluídos'
            ]);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao limpar contatos');
        }
    }
}