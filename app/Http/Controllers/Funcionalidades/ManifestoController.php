<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionalidades\Manifesto;

class ManifestoController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'OK'], 200);
    }

    public function search()
    {
        return response()->json(
            Manifesto::orderByDesc('last_update')->get(),
            200
        );
    }

    public function filter(int $id)
    {
        $manifesto = Manifesto::find($id);

        if (!$manifesto) {
            return response()->json(['error' => 'Manifesto não encontrado'], 404);
        }

        return response()->json($manifesto, 200);
    }

    public function cad(Request $r)
    {
        $data = $r->validate([
            'cliente_id'      => 'required|integer|exists:clientes,id',
            'cliente_backup'  => 'required|string|max:255',
            'filial'          => 'required|string|max:255',
            'destino'         => 'required|string|max:255',
            'motorista'       => 'required|string|max:255',
            'tipo_veiculo'    => 'required|string|max:255',
            'placa_cavalo'    => 'required|string|max:255',
            'observacao'      => 'required|string|max:255',
            'valor'           => 'required|string|max:255',
            'porcentagem'     => 'required|string|max:255',
            'rota'            => 'required|string|max:255',
            'tag'             => 'required|string|max:255',
            'antt'            => 'required|string|max:255',
            'doc_antt'        => 'required|string|max:255',
            'responsavel'     => 'required|string|max:255',
            'desconto'        => 'required|string|max:255',
            'favorecido'      => 'required|string|max:255',

            // opcionais
            'ciot'            => 'nullable|string|max:255',
            'placa_carreta'   => 'nullable|string|max:255',
            'entrega'         => 'nullable|string|max:255',
            'data_entrega'    => 'nullable|date',
            'tributos'        => 'nullable|string|max:255',
            'banco'           => 'nullable|string|max:255',
            'agencia'         => 'nullable|string|max:255',
            'conta'           => 'nullable|string|max:255',
            'tipo_pix'        => 'nullable|string|max:255',
            'chave_pix'       => 'nullable|string|max:255',

            'user_id'         => 'required|integer|exists:users,id',
        ]);

        // proteção PostgreSQL
        $data['data_entrega'] = $data['data_entrega'] ?? null;

        try {
            $manifesto = Manifesto::create($data);
            return response()->json($manifesto, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar manifesto',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $r, int $id)
    {
        $manifesto = Manifesto::find($id);

        if (!$manifesto) {
            return response()->json(['error' => 'Manifesto não encontrado'], 404);
        }

        $data = $r->validate([
            'cliente_id'      => 'required|integer|exists:clientes,id',
            'cliente_backup'  => 'required|string|max:255',
            'filial'          => 'required|string|max:255',
            'destino'         => 'required|string|max:255',
            'motorista'       => 'required|string|max:255',
            'tipo_veiculo'    => 'required|string|max:255',
            'placa_cavalo'    => 'required|string|max:255',
            'observacao'      => 'required|string|max:255',
            'valor'           => 'required|string|max:255',
            'porcentagem'     => 'required|string|max:255',
            'rota'            => 'required|string|max:255',
            'tag'             => 'required|string|max:255',
            'antt'            => 'required|string|max:255',
            'doc_antt'        => 'required|string|max:255',
            'responsavel'     => 'required|string|max:255',
            'desconto'        => 'required|string|max:255',
            'favorecido'      => 'required|string|max:255',

            'ciot'            => 'nullable|string|max:255',
            'placa_carreta'   => 'nullable|string|max:255',
            'entrega'         => 'nullable|string|max:255',
            'data_entrega'    => 'nullable|date',
            'tributos'        => 'nullable|string|max:255',
            'banco'           => 'nullable|string|max:255',
            'agencia'         => 'nullable|string|max:255',
            'conta'           => 'nullable|string|max:255',
            'tipo_pix'        => 'nullable|string|max:255',
            'chave_pix'       => 'nullable|string|max:255',
        ]);

        $data['data_entrega'] = $data['data_entrega'] ?? null;

        try {
            $manifesto->update($data);
            return response()->json($manifesto, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar manifesto',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(int $id)
    {
        $manifesto = Manifesto::find($id);

        if (!$manifesto) {
            return response()->json(['error' => 'Manifesto não encontrado'], 404);
        }

        $manifesto->delete();
        return response()->json(['message' => 'Manifesto excluído com sucesso'], 200);
    }
}
