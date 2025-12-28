<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referencias\Estado;

class EstadosController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => "OK",
        ]);
    }

    public function search()
    {
        try {
            $estados = Estado::all();
            return response()->json($estados, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function filter($id)
    {
        $estado = Estado::find($id);

        if (!$estado) {
            return response()->json(['error' => 'Estado não encontrado'], 404);
        }

        return response()->json($estado, 200);
    }

    public function cad(Request $requisitar)
    {
        $valide = $requisitar->validate([
            'state_name' => 'required|string|max:255',
            'state_sigla' => 'required|string|max:255',
            'user_id' => 'required|integer',
        ]);
        
        try {
            // Criação do registro
            $estado = Estado::create($valide);
            return response()->json($estado, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao adicionar estado: ' . $e->getMessage()], 500);
        }
    }

    public function edit(Request $requisitar, $id)
    {
        $estado = Estado::find($id);

        if (!$estado) {
            return response()->json(['error' => 'Estado não encontrado'], 404);
        }

        $valide = $requisitar->validate([
            'state_name' => 'required|string|max:255',
            'state_sigla' => 'required|string|max:255',
        ]);

        try {
            // Atualização do registro
            $estado->update($valide);
            return response()->json($estado, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar estado: ' . $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $estado = Estado::find($id);

        if (!$estado) {
            return response()->json(['error' => 'Estado não encontrado'], 404);
        }

        $estado->delete();
        return response()->json(['message' => 'Estado excluído com sucesso'], 200);
    }

    public function filterEstados(Request $request)
    {
        $ids = $request->input('ids');
        $estados = Estado::whereIn('id', $ids)->get();
        return response()->json($estados);
    }

}
