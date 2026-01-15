<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\BaseApiController;
use App\Models\Funcionalidades\Mensagem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class MensagensController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    /**
     * Lista mensagens
     */
    public function search()
    {
        try {
            $mensagens = Mensagem::with([
                    'user:id,name',
                    'cliente:id,nome'
                ])
                ->orderByDesc('last_update')
                ->get()
                ->map(fn ($m) => [
                    'id'          => $m->id,
                    'cliente_id'  => $m->cliente_id,
                    'cliente'     => $m->cliente?->nome,
                    'title'       => $m->title,
                    'texto'       => $m->texto,
                    'user_id'     => $m->user_id,
                    'user'        => $m->user?->name,
                    'last_update' => $m->last_update,
                ]);

            return $this->success($mensagens);

        } catch (Throwable $e) {
            return $this->exception($e, '[MENSAGENS] Erro ao listar');
        }
    }

    /**
     * Criação
     */
    public function cad(Request $request)
    {
        try {
            $data = $request->validate([
                'cliente_id' => 'nullable|integer|exists:clientes,id',
                'title'      => 'required|string|max:255',
                'texto'      => 'nullable|string',
                'user_id'    => 'required|integer|exists:users,id',
            ]);

            $mensagem = Mensagem::create($data);

            return $this->success([
                'id' => $mensagem->id,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors'  => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            return $this->exception($e, '[MENSAGENS] Erro ao criar', [
                'payload' => $request->all(),
            ]);
        }
    }

    /**
     * Exclusão
     */
    public function delete(int $id)
    {
        try {
            $mensagem = Mensagem::find($id);

            if (!$mensagem) {
                return $this->error('Mensagem não encontrada', 404);
            }

            $mensagem->delete();

            return $this->success(['message' => 'Mensagem excluída']);

        } catch (Throwable $e) {
            return $this->exception($e, '[MENSAGENS] Erro ao excluir', [
                'id' => $id,
            ]);
        }
    }
}