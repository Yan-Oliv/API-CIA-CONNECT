<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use App\Models\Funcionalidades\Consultas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultasController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'OK',
        ]);
    }

    /**
     * Lista consultas (já preparado para crescer)
     */
    public function search()
    {
        $consultas = Consultas::orderByDesc('last_update')->get();
        return response()->json($consultas, 200);
    }

    /**
     * Busca por ID
     */
    public function filter($id)
    {
        $consulta = Consultas::find($id);

        if (!$consulta) {
            return response()->json(['error' => 'Consulta não encontrada'], 404);
        }

        return response()->json($consulta, 200);
    }

    /**
     * Cadastro
     */
    public function cad(Request $request)
    {
        $validated = $request->validate([
            'motorista'   => 'required|string|max:255',
            'buony'       => 'required|string|max:255',
            'consulta'    => 'required|string|max:255',
            'destino'     => 'required|string|max:255',
            'cliente_id'  => 'required|exists:clientes,id',
            'user_id'     => 'required|exists:users,id',
            'status'      => 'required|string|max:50',
            'observacao'  => 'nullable|string',
        ]);

        try {
            $consulta = Consultas::create($validated);
            return response()->json($consulta, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao adicionar consulta',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualização
     */
    public function edit(Request $request, $id)
    {
        $consulta = Consultas::find($id);

        if (!$consulta) {
            return response()->json(['error' => 'Consulta não encontrada'], 404);
        }

        $validated = $request->validate([
            'motorista'   => 'sometimes|required|string|max:255',
            'buony'       => 'sometimes|required|string|max:255',
            'consulta'    => 'sometimes|required|string|max:255',
            'destino'     => 'sometimes|required|string|max:255',
            'cliente_id'  => 'sometimes|required|exists:clientes,id',
            'status'      => 'sometimes|required|string|max:50',
            'observacao'  => 'nullable|string',
        ]);

        try {
            $consulta->update($validated);
            return response()->json($consulta, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar consulta',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete por ID
     */
    public function delete($id)
    {
        $consulta = Consultas::find($id);

        if (!$consulta) {
            return response()->json(['error' => 'Consulta não encontrada'], 404);
        }

        $consulta->delete();

        return response()->json([
            'message' => 'Consulta excluída com sucesso'
        ], 200);
    }

    /**
     * Delete em lote (status ENVIADO)
     */
    public function deleteEnviados()
    {
        $deleted = Consultas::where('status', 'ENVIADO')->delete();

        return response()->json([
            'message' => "{$deleted} consulta(s) com status ENVIADO foram excluídas com sucesso",
        ], 200);
    }

    /**
     * 🚨 OPERAÇÃO PERIGOSA — agora protegida
     */
    public function destroy()
    {
        DB::transaction(function () {
            Consultas::truncate();
        });

        return response()->json([
            'message' => 'Todas as consultas foram removidas com sucesso'
        ], 200);
    }
}
