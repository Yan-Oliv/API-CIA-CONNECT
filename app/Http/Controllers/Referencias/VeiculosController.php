<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referencias\Veiculo;

class VeiculosController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK'], 200);
    }

    public function search()
    {
        $veiculos = Veiculo::orderBy('nome')->get();
        return response()->json($veiculos, 200);
    }

    public function filter($id)
    {
        $veiculo = Veiculo::find($id);

        if (!$veiculo) {
            return response()->json(['error' => 'Veículo não encontrado'], 404);
        }

        return response()->json($veiculo, 200);
    }

    public function cad(Request $request)
    {
        $validated = $request->validate([
            'nome'    => 'required|string|max:255',
            'tipo'    => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $veiculo = Veiculo::create($validated);
            return response()->json($veiculo, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar veículo',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $veiculo = Veiculo::find($id);

        if (!$veiculo) {
            return response()->json(['error' => 'Veículo não encontrado'], 404);
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
        ]);

        try {
            $veiculo->update($validated);
            return response()->json($veiculo, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar veículo',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $veiculo = Veiculo::find($id);

        if (!$veiculo) {
            return response()->json(['error' => 'Veículo não encontrado'], 404);
        }

        $veiculo->delete();
        return response()->json(['message' => 'Veículo excluído com sucesso'], 200);
    }

    public function filterVeiculos(Request $request)
    {
        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'IDs inválidos'], 400);
        }

        $veiculos = Veiculo::whereIn('id', $ids)->get();
        return response()->json($veiculos, 200);
    }
}
