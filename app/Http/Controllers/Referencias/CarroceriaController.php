<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referencias\Carroceria;

class CarroceriaController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'OK',
        ]);
    }

    public function search()
    {
        try {
            $carrocerias = Carroceria::orderBy('id')->get();
            return response()->json($carrocerias, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao buscar carrocerias',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function filter($id)
    {
        $carroceria = Carroceria::find($id);

        if (!$carroceria) {
            return response()->json(['error' => 'Carroceria não encontrada'], 404);
        }

        return response()->json($carroceria, 200);
    }

    public function cad(Request $request)
    {
        $valide = $request->validate([
            'nome'    => 'required|string|max:255',
            'tipo'    => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $carroceria = Carroceria::create($valide);
            return response()->json($carroceria, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao adicionar carroceria',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $carroceria = Carroceria::find($id);

        if (!$carroceria) {
            return response()->json(['error' => 'Carroceria não encontrada'], 404);
        }

        $valide = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
        ]);

        try {
            $carroceria->update($valide);
            return response()->json($carroceria, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar carroceria',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        $carroceria = Carroceria::find($id);

        if (!$carroceria) {
            return response()->json(['error' => 'Carroceria não encontrada'], 404);
        }

        try {
            $carroceria->delete();
            return response()->json(['message' => 'Carroceria excluída com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir carroceria',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function filterCarrocerias(Request $request)
    {
        $valide = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $carrocerias = Carroceria::whereIn('id', $valide['ids'])
            ->orderBy('id')
            ->get();

        return response()->json($carrocerias, 200);
    }
}
