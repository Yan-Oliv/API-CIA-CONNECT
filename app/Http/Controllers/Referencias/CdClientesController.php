<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Models\Referencias\CdCliente;
use Illuminate\Http\Request;

class CdClientesController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    /**
     * Lista todos os CDs
     * Ordem explícita para evitar comportamento indefinido no PostgreSQL
     */
    public function search()
    {
        try {
            $cds = CdCliente::orderBy('id')->get();
            return response()->json($cds, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao buscar CDs',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function filter($id)
    {
        $cd = CdCliente::find($id);

        if (!$cd) {
            return response()->json(['error' => 'CD não encontrado'], 404);
        }

        return response()->json($cd, 200);
    }

    /**
     * Criação
     * Validação forte para FK (PostgreSQL não perdoa)
     */
    public function cad(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'  => 'required|integer|exists:clientes,id',
            'nome_filial' => 'required|string|max:255',
            'cidade'      => 'required|string|max:100',
            'estado'      => 'required|string|size:2',
        ]);

        try {
            $cd = CdCliente::create($validated);
            return response()->json($cd, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao adicionar CD',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualização
     */
    public function edit(Request $request, $id)
    {
        $cd = CdCliente::find($id);

        if (!$cd) {
            return response()->json(['error' => 'CD não encontrado'], 404);
        }

        $validated = $request->validate([
            'nome_filial' => 'required|string|max:255',
            'cidade'      => 'required|string|max:100',
            'estado'      => 'required|string|size:2',
        ]);

        try {
            $cd->update($validated);
            return response()->json($cd, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar CD',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exclusão
     */
    public function delete($id)
    {
        $cd = CdCliente::find($id);

        if (!$cd) {
            return response()->json(['error' => 'CD não encontrado'], 404);
        }

        $cd->delete();
        return response()->json(['message' => 'CD excluído com sucesso'], 200);
    }

    /**
     * Filtro por lista de IDs
     * PostgreSQL-safe
     */
    public function filterCds(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $cds = CdCliente::whereIn('id', $validated['ids'])
            ->orderBy('id')
            ->get();

        return response()->json($cds, 200);
    }
}
