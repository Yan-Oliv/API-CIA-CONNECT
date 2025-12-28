<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referencias\Filial;

class FilialController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    public function search()
    {
        return response()->json(
            Filial::orderByDesc('last_update')->get(),
            200
        );
    }

    public function filter($id)
    {
        $filial = Filial::find($id);

        if (!$filial) {
            return response()->json(['error' => 'Filial não encontrada'], 404);
        }

        return response()->json($filial, 200);
    }

    public function cad(Request $request)
    {
        $validated = $request->validate([
            'filial'  => 'required|string|max:255',
            'estado'  => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $filial = Filial::create($validated);

            return response()->json($filial, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao adicionar filial',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $filial = Filial::find($id);

        if (!$filial) {
            return response()->json(['error' => 'Filial não encontrada'], 404);
        }

        $validated = $request->validate([
            'filial' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
        ]);

        try {
            $filial->update($validated);

            return response()->json($filial, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar filial',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $filial = Filial::find($id);

        if (!$filial) {
            return response()->json(['error' => 'Filial não encontrada'], 404);
        }

        try {
            $filial->delete();

            return response()->json([
                'message' => 'Filial excluída com sucesso'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir filial',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function filterFilials(Request $request)
    {
        $ids = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ])['ids'];

        return response()->json(
            Filial::whereIn('id', $ids)->get(),
            200
        );
    }
}
