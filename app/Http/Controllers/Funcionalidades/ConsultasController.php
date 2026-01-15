<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\BaseApiController;
use App\Models\Funcionalidades\Consultas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ConsultasController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $consultas = Consultas::orderByDesc('last_update')->get();
            return $this->success($consultas);
        } catch (Throwable $e) {
            return $this->exception($e, '[CONSULTAS] Erro ao listar');
        }
    }

    public function filter($id)
    {
        try {
            $consulta = Consultas::find($id);

            if (!$consulta) {
                return $this->error('Consulta não encontrada', 404);
            }

            return $this->success($consulta);
        } catch (Throwable $e) {
            return $this->exception($e, '[CONSULTAS] Erro ao buscar', ['id' => $id]);
        }
    }

    public function cad(Request $request)
    {
        try {
            $dados = $request->validate([
                'motorista'   => 'required|string|max:255',
                'buony'       => 'required|string|max:255',
                'consulta'    => 'required|string|max:255',
                'destino'     => 'required|string|max:255',
                'cliente_id'  => 'required|exists:clientes,id',
                'user_id'     => 'required|exists:users,id',
                'status'      => 'required|string|max:50',
                'observacao'  => 'nullable|string',
            ]);

            DB::beginTransaction();

            $consulta = Consultas::create($dados);

            DB::commit();

            Log::info('[CONSULTAS] Criada', [
                'id' => $consulta->id,
                'user_id' => $dados['user_id'],
            ]);

            return $this->success($consulta, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();
            return $this->exception($e, '[CONSULTAS] Erro ao criar', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $consulta = Consultas::find($id);

            if (!$consulta) {
                return $this->error('Consulta não encontrada', 404);
            }

            $dados = $request->validate([
                'motorista'   => 'sometimes|required|string|max:255',
                'buony'       => 'sometimes|required|string|max:255',
                'consulta'    => 'sometimes|required|string|max:255',
                'destino'     => 'sometimes|required|string|max:255',
                'cliente_id'  => 'sometimes|required|exists:clientes,id',
                'status'      => 'sometimes|required|string|max:50',
                'observacao'  => 'nullable|string',
            ]);

            DB::beginTransaction();

            $consulta->update($dados);

            DB::commit();

            Log::info('[CONSULTAS] Atualizada', ['id' => $id]);

            return $this->success($consulta);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();
            return $this->exception($e, '[CONSULTAS] Erro ao atualizar', ['id' => $id]);
        }
    }

    public function delete($id)
    {
        try {
            $consulta = Consultas::find($id);

            if (!$consulta) {
                return $this->error('Consulta não encontrada', 404);
            }

            $consulta->delete();

            Log::info('[CONSULTAS] Excluída', ['id' => $id]);

            return $this->success(['message' => 'Consulta excluída com sucesso']);

        } catch (Throwable $e) {
            return $this->exception($e, '[CONSULTAS] Erro ao excluir', ['id' => $id]);
        }
    }
}