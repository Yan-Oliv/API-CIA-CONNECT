<?php

namespace App\Http\Controllers\Utilities;

use App\Http\Controllers\Controller;
use App\Models\Utilities\Links;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LinksController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    public function search()
    {
        return response()->json(
            Links::orderByDesc('last_update')->get(),
            200
        );
    }

    public function filter($id)
    {
        $link = Links::find($id);

        if (!$link) {
            return response()->json(['error' => 'Link não encontrado'], 404);
        }

        return response()->json($link, 200);
    }

    public function cad(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'desc'    => 'nullable|string|max:255',
            'link'    => 'nullable|string',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $link = Links::create($validated);
                return response()->json($link, 201);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao adicionar link',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $link = Links::find($id);

        if (!$link) {
            return response()->json(['error' => 'Link não encontrado'], 404);
        }

        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'desc'    => 'nullable|string|max:255',
            'link'    => 'nullable|string',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            return DB::transaction(function () use ($link, $validated) {
                $link->update($validated);
                return response()->json($link, 200);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar link',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⚠️ CUIDADO: método destrutivo
     */
    public function destroy()
    {
        Links::query()->delete();
        return response()->json([
            'message' => 'Todos os links foram excluídos com sucesso'
        ], 200);
    }

    public function delete($id)
    {
        $link = Links::find($id);

        if (!$link) {
            return response()->json(['error' => 'Link não encontrado'], 404);
        }

        $link->delete();
        return response()->json(['message' => 'Link excluído com sucesso'], 200);
    }
}
