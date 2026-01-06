<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionalidades\Mensagem;
use Illuminate\Support\Str;

class MensagensController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    /**
     * Lista mensagens
     */
    public function search()
    {
        $mensagens = Mensagem::with([
                'user:id,name',
                'cliente:id,nome'
            ])
            ->orderByDesc('last_update')
            ->get();

        $result = $mensagens->map(function ($mensagem) {
            return [
                'id'          => $mensagem->id,
                'cliente_id'  => $mensagem->cliente_id,
                'cliente'     => $mensagem->cliente?->nome,
                'title'       => $mensagem->title,
                'texto'       => $mensagem->texto,
                'user_id'     => $mensagem->user_id,
                'user'        => $mensagem->user?->name,
                'last_update' => $mensagem->last_update,
            ];
        });

        return response()->json($result, 200);
    }

    /**
     * Criação
     */
    public function cad(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'title'      => 'required|string|max:255',
            'texto'      => 'nullable|string',
            'user_id'    => 'required|integer|exists:users,id',
        ]);

        $mensagem = Mensagem::create($validated);

        return response()->json([
            'message' => 'Mensagem criada com sucesso',
            'id'      => $mensagem->id,
        ], 201);
    }

    /**
     * Exclusão
     */
    public function delete(int $id)
    {
        $mensagem = Mensagem::find($id);

        if (!$mensagem) {
            return response()->json(['error' => 'Mensagem não encontrada'], 404);
        }

        $mensagem->delete();

        return response()->json(['message' => 'Mensagem excluída com sucesso'], 200);
    }
}
