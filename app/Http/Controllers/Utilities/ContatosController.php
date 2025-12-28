<?php

namespace App\Http\Controllers\Utilities;

use App\Http\Controllers\Controller;
use App\Models\Utilities\Contatos;
use Illuminate\Http\Request;

class ContatosController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK'], 200);
    }

    public function search()
    {
        $contatos = Contatos::orderByDesc('last_update')->get();
        return response()->json($contatos, 200);
    }

    public function filter(int $id)
    {
        $contato = Contatos::find($id);

        if (!$contato) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }

        return response()->json($contato, 200);
    }

    public function cad(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'desc'    => 'nullable|string|max:255',
            'numero'  => 'nullable|string|max:50',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $contato = Contatos::create($validated);
            return response()->json($contato, 201);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao adicionar contato',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request, int $id)
    {
        $contato = Contatos::find($id);

        if (!$contato) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }

        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'desc'    => 'nullable|string|max:255',
            'numero'  => 'nullable|string|max:50',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $contato->update($validated);
            return response()->json($contato, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar contato',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ⚠️ Use com EXTREMO cuidado
     * Idealmente remova em produção
     */
    public function destroy()
    {
        Contatos::truncate();
        return response()->json([
            'message' => 'Todos os contatos foram excluídos com sucesso'
        ], 200);
    }

    public function delete(int $id)
    {
        $contato = Contatos::find($id);

        if (!$contato) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }

        $contato->delete();
        return response()->json(['message' => 'Contato excluído com sucesso'], 200);
    }
}
