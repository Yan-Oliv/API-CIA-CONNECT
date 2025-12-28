<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use App\Models\Funcionalidades\Motoristas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MotoristasController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK'], 200);
    }

    public function search()
    {
        $motoristas = Motoristas::orderByDesc('last_update')->get();
        return response()->json($motoristas, 200);
    }

    public function filter(int $id)
    {
        $motorista = Motoristas::find($id);

        if (!$motorista) {
            return response()->json(['error' => 'Motorista não encontrado'], 404);
        }

        return response()->json($motorista, 200);
    }

    public function cad(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:20',
            'vei_id' => 'required|integer|exists:veiculos,id',
            'user_id' => 'required|integer|exists:users,id',

            'car_id' => 'nullable|integer|exists:carrocerias,id',
            'quantidade_paletes' => 'nullable|integer',
            'peso' => 'nullable|numeric',
            'metragem_cubica' => 'nullable|numeric',
            'placa_cavalo' => 'nullable|string|max:20',
            'placa_reboque' => 'nullable|string|max:20',
            'placa_segundo' => 'nullable|string|max:20',
            'placa_terceiro' => 'nullable|string|max:20',
            'antt' => 'nullable|string|max:50',
            'doc_cavalo' => 'nullable|string|max:50',
            'cpf' => 'nullable|string|max:20|unique:motoristas,cpf',
            'banco' => 'nullable|string|max:100',
            'agencia' => 'nullable|string|max:20',
            'conta' => 'nullable|string|max:30',
            'pix' => 'nullable|string|max:100',
            'tipo_pix' => 'nullable|string|max:30',
            'beneficiario' => 'nullable|string|max:100',
            'telefone_patrao' => 'nullable|string|max:20',
            'tag' => 'nullable|string|max:50',
            'eixos' => 'nullable|integer',
            'mopp' => 'nullable|string|max:255',
            'rastreador' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'observacao' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $motorista = Motoristas::create($validated);
            DB::commit();

            return response()->json($motorista, 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erro ao adicionar motorista',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, int $id)
    {
        $motorista = Motoristas::find($id);

        if (!$motorista) {
            return response()->json(['error' => 'Motorista não encontrado'], 404);
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:20',
            'vei_id' => 'required|integer|exists:veiculos,id',

            'car_id' => 'nullable|integer|exists:carrocerias,id',
            'quantidade_paletes' => 'nullable|integer',
            'peso' => 'nullable|numeric',
            'metragem_cubica' => 'nullable|numeric',
            'placa_cavalo' => 'nullable|string|max:20',
            'placa_reboque' => 'nullable|string|max:20',
            'placa_segundo' => 'nullable|string|max:20',
            'placa_terceiro' => 'nullable|string|max:20',
            'antt' => 'nullable|string|max:50',
            'doc_cavalo' => 'nullable|string|max:50',
            'cpf' => 'nullable|string|max:20|unique:motoristas,cpf,' . $id,
            'banco' => 'nullable|string|max:100',
            'agencia' => 'nullable|string|max:20',
            'conta' => 'nullable|string|max:30',
            'pix' => 'nullable|string|max:100',
            'tipo_pix' => 'nullable|string|max:30',
            'beneficiario' => 'nullable|string|max:100',
            'telefone_patrao' => 'nullable|string|max:20',
            'tag' => 'nullable|string|max:50',
            'eixos' => 'nullable|integer',
            'mopp' => 'nullable|string|max:255',
            'rastreador' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'observacao' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $motorista->update($validated);
            DB::commit();

            return response()->json($motorista, 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erro ao atualizar motorista',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(int $id)
    {
        $motorista = Motoristas::find($id);

        if (!$motorista) {
            return response()->json(['error' => 'Motorista não encontrado'], 404);
        }

        $motorista->delete();
        return response()->json(['message' => 'Motorista excluído com sucesso'], 200);
    }
}