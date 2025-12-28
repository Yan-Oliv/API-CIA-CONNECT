<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Models\Referencias\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    public function search()
    {
        try {
            return response()->json(
                Cliente::with('gestores')->orderBy('id')->get(),
                200
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function filter($id)
    {
        $cliente = Cliente::with('gestores')->find($id);

        if (!$cliente) {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        return response()->json($cliente, 200);
    }

    public function cad(Request $request)
    {
        $data = $request->validate([
            'nome'        => 'required|string|max:255',
            'user_id'     => 'required|integer|exists:users,id',
            'gestores'    => 'nullable|array',
            'gestores.*'  => 'integer|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $cliente = Cliente::create([
                'nome'    => $data['nome'],
                'user_id' => $data['user_id'],
            ]);

            if (!empty($data['gestores'])) {
                $cliente->gestores()->sync($data['gestores']);
            }

            DB::commit();

            return response()->json(
                $cliente->load('gestores'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erro ao adicionar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $data = $request->validate([
            'nome'        => 'required|string|max:255',
            'gestores'    => 'nullable|array',
            'gestores.*'  => 'integer|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $cliente->update([
                'nome' => $data['nome'],
            ]);

            if (array_key_exists('gestores', $data)) {
                $cliente->gestores()->sync($data['gestores'] ?? []);
            }

            DB::commit();

            return response()->json(
                $cliente->load('gestores'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erro ao atualizar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente excluído com sucesso'], 200);
    }

    public function filterClientes(Request $request)
    {
        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'IDs inválidos ou ausentes'], 400);
        }

        return response()->json(
            Cliente::whereIn('id', $ids)->get(),
            200
        );
    }
}
