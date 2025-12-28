<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionalidades\Cargas;

class CargasController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    public function search()
    {
        return response()->json(Cargas::all(), 200);
    }

    public function filter($id)
    {
        $carga = Cargas::find($id);

        if (!$carga) {
            return response()->json(['error' => 'Carga não encontrada'], 404);
        }

        return response()->json($carga, 200);
    }

    public function cad(Request $request)
    {
        $data = $request->validate([
            'cod_carga' => 'required|string|max:255|unique:cargas,cod_carga',
            'titulo' => 'required|string|max:255',
            'produto' => 'required|string|max:255',

            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'cliente_backup' => 'nullable|string|max:255',

            'tipo_carregamento' => 'required|string|max:255',
            'tamanho_veiculo' => 'required|integer',
            'status' => 'required|string|max:255',

            'cidade_origem' => 'required|string|max:255',
            'cidade_destino' => 'required|string|max:255',

            'uf_id' => 'required|integer|exists:ufs,id',

            'carregamento' => 'required|date',
            'descarga' => 'nullable|string|max:255',

            'peso' => 'required|integer',
            'valor' => 'required|string|max:255',
            'adiantamento' => 'nullable|string|max:255',
            'observacao' => 'nullable|string',

            'filial_id' => 'nullable|integer|exists:filiais,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Fallback seguro
        if (empty($data['cliente_id'])) {
            $data['cliente_id'] = null;
        }

        try {
            $carga = Cargas::create($data);
            return response()->json($carga, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar carga',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $carga = Cargas::find($id);

        if (!$carga) {
            return response()->json(['error' => 'Carga não encontrada'], 404);
        }

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'produto' => 'required|string|max:255',

            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'cliente_backup' => 'nullable|string|max:255',

            'tipo_carregamento' => 'required|string|max:255',
            'tamanho_veiculo' => 'required|integer',
            'status' => 'required|string|max:255',

            'cidade_origem' => 'required|string|max:255',
            'cidade_destino' => 'required|string|max:255',

            'uf_id' => 'required|integer|exists:ufs,id',

            'carregamento' => 'required|date',
            'descarga' => 'nullable|string|max:255',

            'peso' => 'required|integer',
            'valor' => 'required|string|max:255',
            'adiantamento' => 'nullable|string|max:255',
            'observacao' => 'nullable|string',

            'filial_id' => 'nullable|integer|exists:filiais,id',
        ]);

        try {
            $carga->update($data);
            return response()->json($carga, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar carga',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        $carga = Cargas::find($id);

        if (!$carga) {
            return response()->json(['error' => 'Carga não encontrada'], 404);
        }

        $carga->delete();
        return response()->json(['message' => 'Carga excluída com sucesso'], 200);
    }
}
