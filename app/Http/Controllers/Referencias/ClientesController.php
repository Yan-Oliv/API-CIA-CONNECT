<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\BaseApiController;
use App\Models\Referencias\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClientesController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $clientes = Cliente::with('gestores')
                ->orderBy('id')
                ->get();

            return $this->success($clientes);

        } catch (Throwable $e) {
            return $this->exception(
                $e,
                'Erro ao listar clientes',
                ['rota' => 'clis/s']
            );
        }
    }

    public function filter(int $id)
    {
        $cliente = Cliente::with('gestores')->find($id);

        if (!$cliente) {
            return $this->error('Cliente não encontrado', 404);
        }

        return $this->success($cliente);
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

            return $this->success(
                $cliente->load('gestores'),
                201
            );

        } catch (Throwable $e) {
            DB::rollBack();

            return $this->exception(
                $e,
                'Erro ao criar cliente',
                ['payload' => $data]
            );
        }
    }

    public function edit(Request $request, int $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return $this->error('Cliente não encontrado', 404);
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

            return $this->success(
                $cliente->load('gestores')
            );

        } catch (Throwable $e) {
            DB::rollBack();

            return $this->exception(
                $e,
                'Erro ao atualizar cliente',
                ['cliente_id' => $id]
            );
        }
    }

    public function delete(int $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return $this->error('Cliente não encontrado', 404);
        }

        try {
            $cliente->delete();
            return $this->success(['message' => 'Cliente excluído']);

        } catch (Throwable $e) {
            return $this->exception(
                $e,
                'Erro ao excluir cliente',
                ['cliente_id' => $id]
            );
        }
    }

    public function filterClientes(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        try {
            return $this->success(
                Cliente::whereIn('id', $data['ids'])->get()
            );

        } catch (Throwable $e) {
            return $this->exception(
                $e,
                'Erro ao filtrar clientes',
                ['ids' => $data['ids']]
            );
        }
    }
}