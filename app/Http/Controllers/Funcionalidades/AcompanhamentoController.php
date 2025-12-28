<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Funcionalidades\Acompanhamento;

class AcompanhamentoController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    public function search()
    {
        return response()->json(Acompanhamento::all(), 200);
    }

    public function filter($id)
    {
        $acompanhamento = Acompanhamento::find($id);

        if (!$acompanhamento) {
            return response()->json(['error' => 'Acompanhamento não encontrado'], 404);
        }

        return response()->json($acompanhamento, 200);
    }

    public function cad(Request $request)
    {
        $dados = $request->validate([
            'cte' => 'nullable|string|max:255',
            'motorista' => 'required|string|max:255',
            'contato_motorista' => 'nullable|string|max:255',
            'telefone_motorista' => 'nullable|string|max:255',
            'cliente_id' => 'required|exists:clientes,id',
            'produto' => 'nullable|string|max:255',
            'valor_negociado' => 'nullable|string|max:255',
            'origem' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'dia_carregamento' => 'nullable|string|max:255',
            'agenda_descarga' => 'nullable|string|max:255',
            'data_chegada' => 'nullable|string|max:255',
            'hora_chegada' => 'nullable|string|max:255',
            'nome_patrao' => 'nullable|string|max:255',
            'telefone_patrao' => 'nullable|string|max:255',
            'veiculo_id' => 'nullable|integer',
            'status' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            $acompanhamento = Acompanhamento::create($dados);
            DB::commit();

            return response()->json($acompanhamento, 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erro ao criar acompanhamento',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $acompanhamento = Acompanhamento::find($id);

        if (!$acompanhamento) {
            return response()->json(['error' => 'Acompanhamento não encontrado'], 404);
        }

        $dados = $request->validate([
            'cte' => 'nullable|string|max:255',
            'motorista' => 'required|string|max:255',
            'contato_motorista' => 'nullable|string|max:255',
            'telefone_motorista' => 'nullable|string|max:255',
            'cliente_id' => 'required|exists:clientes,id',
            'produto' => 'nullable|string|max:255',
            'valor_negociado' => 'nullable|string|max:255',
            'origem' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'dia_carregamento' => 'nullable|string|max:255',
            'agenda_descarga' => 'nullable|string|max:255',
            'data_chegada' => 'nullable|string|max:255',
            'hora_chegada' => 'nullable|string|max:255',
            'nome_patrao' => 'nullable|string|max:255',
            'telefone_patrao' => 'nullable|string|max:255',
            'veiculo_id' => 'nullable|integer',
            'status' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            $acompanhamento->update($dados);
            DB::commit();

            return response()->json($acompanhamento, 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erro ao atualizar acompanhamento',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        $acompanhamento = Acompanhamento::find($id);

        if (!$acompanhamento) {
            return response()->json(['error' => 'Acompanhamento não encontrado'], 404);
        }

        try {
            $acompanhamento->delete();
            return response()->json(['message' => 'Acompanhamento excluído com sucesso'], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao excluir acompanhamento',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}