<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referencias\Requisitos;

class RequisitosController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    public function search()
    {
        try {
            return response()->json(
                Requisitos::orderByDesc('last_update')->get(),
                200
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function filter($id)
    {
        $requisito = Requisitos::find($id);

        if (!$requisito) {
            return response()->json(['error' => 'Requisito não encontrado'], 404);
        }

        return response()->json($requisito, 200);
    }

    public function cad(Request $request)
    {
        $validated = $request->validate([
            'requisitos' => 'required|string|max:255',
            'user_id'    => 'required|integer|exists:users,id',
        ]);

        try {
            return response()->json(
                Requisitos::create($validated),
                201
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao adicionar requisito: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $requisito = Requisitos::find($id);

        if (!$requisito) {
            return response()->json(['error' => 'Requisito não encontrado'], 404);
        }

        $validated = $request->validate([
            'requisitos' => 'required|string|max:255',
        ]);

        try {
            $requisito->update($validated);
            return response()->json($requisito, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar requisito: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        $requisito = Requisitos::find($id);

        if (!$requisito) {
            return response()->json(['error' => 'Requisito não encontrado'], 404);
        }

        $requisito->delete();

        return response()->json(['message' => 'Requisito excluído com sucesso'], 200);
    }

    public function filterRequisitos(Request $request)
    {
        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'IDs inválidos'], 400);
        }

        return response()->json(
            Requisitos::whereIn('id', $ids)->get(),
            200
        );
    }
}
