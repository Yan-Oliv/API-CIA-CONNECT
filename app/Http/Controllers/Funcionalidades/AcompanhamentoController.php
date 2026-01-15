<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use App\Models\Funcionalidades\Acompanhamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AcompanhamentoController extends Controller
{
    /**
     * INDEX (health simples)
     */
    public function index()
    {
        return response()->json(['status' => 'OK'], 200);
    }

    /**
     * SEARCH
     */
    public function search()
    {
        try {
            return response()->json(
                Acompanhamento::orderBy('id', 'desc')->get(),
                200
            );
        } catch (Throwable $e) {
            Log::error('[ACOMPANHAMENTO] Erro ao listar', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao buscar acompanhamentos',
            ], 500);
        }
    }

    /**
     * FILTER BY ID
     */
    public function filter($id)
    {
        try {
            $acompanhamento = Acompanhamento::find($id);

            if (!$acompanhamento) {
                return response()->json([
                    'message' => 'Acompanhamento não encontrado',
                ], 404);
            }

            return response()->json($acompanhamento, 200);

        } catch (Throwable $e) {
            Log::error('[ACOMPANHAMENTO] Erro ao buscar por ID', [
                'id' => $id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao buscar acompanhamento',
            ], 500);
        }
    }

    /**
     * CREATE
     */
    public function cad(Request $request)
    {
        try {
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

            $acompanhamento = Acompanhamento::create($dados);

            DB::commit();

            Log::info('[ACOMPANHAMENTO] Criado', [
                'id' => $acompanhamento->id,
                'user_id' => $dados['user_id'],
            ]);

            return response()->json($acompanhamento, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('[ACOMPANHAMENTO] Erro ao criar', [
                'payload' => $request->all(),
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro interno ao criar acompanhamento',
            ], 500);
        }
    }

    /**
     * UPDATE
     */
    public function edit(Request $request, $id)
    {
        try {
            $acompanhamento = Acompanhamento::find($id);

            if (!$acompanhamento) {
                return response()->json([
                    'message' => 'Acompanhamento não encontrado',
                ], 404);
            }

            $dados = $request->validate([
                'cte' => 'nullable|string|max:255',
                'motorista' => 'required|string|max:255',
                'cliente_id' => 'required|exists:clientes,id',
                'origem' => 'required|string|max:255',
                'destino' => 'required|string|max:255',
                'status' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            $acompanhamento->update($dados);

            DB::commit();

            Log::info('[ACOMPANHAMENTO] Atualizado', [
                'id' => $id,
            ]);

            return response()->json($acompanhamento, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('[ACOMPANHAMENTO] Erro ao atualizar', [
                'id' => $id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao atualizar acompanhamento',
            ], 500);
        }
    }

    /**
     * DELETE
     */
    public function delete($id)
    {
        try {
            $acompanhamento = Acompanhamento::find($id);

            if (!$acompanhamento) {
                return response()->json([
                    'message' => 'Acompanhamento não encontrado',
                ], 404);
            }

            $acompanhamento->delete();

            Log::info('[ACOMPANHAMENTO] Excluído', [
                'id' => $id,
            ]);

            return response()->json([
                'message' => 'Acompanhamento excluído com sucesso',
            ], 200);

        } catch (Throwable $e) {
            Log::error('[ACOMPANHAMENTO] Erro ao excluir', [
                'id' => $id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao excluir acompanhamento',
            ], 500);
        }
    }
}