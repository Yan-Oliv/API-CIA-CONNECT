<?php

namespace App\Http\Controllers\Utilities;

use App\Http\Controllers\BaseApiController;
use App\Models\Utilities\Links;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class LinksController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $links = Links::orderByDesc('last_update')->get();
            return $this->success($links);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao listar links');
        }
    }

    public function filter(int $id)
    {
        $link = Links::find($id);

        if (!$link) {
            return $this->error('Link não encontrado', 404);
        }

        return $this->success($link);
    }

    public function cad(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'   => 'required|string|max:255',
                'desc'    => 'nullable|string|max:255',
                'link'    => 'nullable|string|max:500',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $link = Links::create($validated);

            return $this->success($link, 201);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao cadastrar link', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function edit(Request $request, int $id)
    {
        $link = Links::find($id);

        if (!$link) {
            return $this->error('Link não encontrado', 404);
        }

        try {
            $validated = $request->validate([
                'title'   => 'required|string|max:255',
                'desc'    => 'nullable|string|max:255',
                'link'    => 'nullable|string|max:500',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $link->update($validated);

            return $this->success($link);

        } catch (ValidationException $e) {
            return $this->error(
                'Dados inválidos',
                422,
                ['errors' => $e->errors()]
            );

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao atualizar link', [
                'id' => $id,
            ]);
        }
    }

    public function delete(int $id)
    {
        $link = Links::find($id);

        if (!$link) {
            return $this->error('Link não encontrado', 404);
        }

        try {
            $link->delete();

            return $this->success([
                'message' => 'Link excluído com sucesso'
            ]);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao excluir link', [
                'id' => $id,
            ]);
        }
    }

    /**
     * ⚠️ EXTREMO CUIDADO
     * Ideal bloquear por role ou ambiente
     */
    public function destroy()
    {
        try {
            Links::truncate();

            return $this->success([
                'message' => 'Todos os links foram excluídos'
            ]);

        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao limpar links');
        }
    }
}