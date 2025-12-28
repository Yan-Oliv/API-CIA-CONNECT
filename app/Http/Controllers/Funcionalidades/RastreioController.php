<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionalidades\Rastreio;

class RastreioController extends Controller
{
    /* ───────────────────────────── *
     *  Healthcheck                  *
     * ───────────────────────────── */
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    /* ───────────────────────────── *
     *  Lista todos                  *
     * ───────────────────────────── */
    public function search()
    {
        return response()->json(
            Rastreio::orderByDesc('last_update')->get(),
            200
        );
    }

    /* ───────────────────────────── *
     *  Busca por ID                 *
     * ───────────────────────────── */
    public function filter(int $id)
    {
        $rastreio = Rastreio::find($id);

        if (!$rastreio) {
            return response()->json(['error' => 'Rastreio não encontrado'], 404);
        }

        return response()->json($rastreio, 200);
    }

    /* ───────────────────────────── *
     *  Criação                      *
     * ───────────────────────────── */
    public function cad(Request $request)
    {
        $validated = $request->validate([
            'dt_id'             => 'nullable|string|max:255',
            'motorista'         => 'nullable|string|max:255',
            'cpf'               => 'nullable|string|max:255',
            'placa_cavalo'      => 'nullable|string|max:255',
            'placa_reboque'     => 'nullable|string|max:255',
            'tipo_rastreador'   => 'nullable|string|max:255',
            'status_rastreador' => 'nullable|string|max:255',
            'buonny'            => 'nullable|string|max:255',
            'brk'               => 'nullable|string|max:255',
            'check_list'        => 'nullable|string|max:255',
            'id_rastreador'     => 'nullable|string|max:255',
            'login_rast'        => 'nullable|string|max:255',
            'pass_rast'         => 'nullable|string|max:255',
            'status'            => 'nullable|string|max:255',
            'user_id'           => 'nullable|integer|exists:users,id',
        ]);

        try {
            $rastreio = Rastreio::create($validated);
            return response()->json($rastreio, 201);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao adicionar rastreio',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    /* ───────────────────────────── *
     *  Atualização                  *
     * ───────────────────────────── */
    public function edit(Request $request, int $id)
    {
        $rastreio = Rastreio::find($id);

        if (!$rastreio) {
            return response()->json(['error' => 'Rastreio não encontrado'], 404);
        }

        $validated = $request->validate([
            'dt_id'             => 'nullable|string|max:255',
            'motorista'         => 'nullable|string|max:255',
            'cpf'               => 'nullable|string|max:255',
            'placa_cavalo'      => 'nullable|string|max:255',
            'placa_reboque'     => 'nullable|string|max:255',
            'tipo_rastreador'   => 'nullable|string|max:255',
            'status_rastreador' => 'nullable|string|max:255',
            'buonny'            => 'nullable|string|max:255',
            'brk'               => 'nullable|string|max:255',
            'check_list'        => 'nullable|string|max:255',
            'id_rastreador'     => 'nullable|string|max:255',
            'login_rast'        => 'nullable|string|max:255',
            'pass_rast'         => 'nullable|string|max:255',
            'status'            => 'nullable|string|max:255',
            'user_id'           => 'nullable|integer|exists:users,id',
        ]);

        try {
            $rastreio->update($validated);
            return response()->json($rastreio, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar rastreio',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    /* ───────────────────────────── *
     *  Exclusão                     *
     * ───────────────────────────── */
    public function delete(int $id)
    {
        $rastreio = Rastreio::find($id);

        if (!$rastreio) {
            return response()->json(['error' => 'Rastreio não encontrado'], 404);
        }

        $rastreio->delete();

        return response()->json(['message' => 'Rastreio excluído com sucesso'], 200);
    }
}