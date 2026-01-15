<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\BaseApiController;
use App\Models\Funcionalidades\Manifesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ManifestoController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search()
    {
        try {
            $manifestos = Manifesto::orderByDesc('last_update')->get();
            return $this->success($manifestos);

        } catch (Throwable $e) {
            return $this->exception($e, '[MANIFESTO] Erro ao listar');
        }
    }

    public function filter(int $id)
    {
        try {
            $manifesto = Manifesto::find($id);

            if (!$manifesto) {
                return $this->error('Manifesto não encontrado', 404);
            }

            return $this->success($manifesto);

        } catch (Throwable $e) {
            return $this->exception($e, '[MANIFESTO] Erro ao buscar', [
                'id' => $id,
            ]);
        }
    }

    public function cad(Request $r)
    {
        try {
            $data = $r->validate($this->rules());

            $data['data_entrega'] = $data['data_entrega'] ?? null;

            DB::beginTransaction();

            $manifesto = Manifesto::create($data);

            DB::commit();

            return $this->success($manifesto, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors'  => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();
            return $this->exception($e, '[MANIFESTO] Erro ao criar', [
                'payload' => $r->all(),
            ]);
        }
    }

    public function edit(Request $r, int $id)
    {
        try {
            $manifesto = Manifesto::find($id);

            if (!$manifesto) {
                return $this->error('Manifesto não encontrado', 404);
            }

            $data = $r->validate($this->rules(false));
            $data['data_entrega'] = $data['data_entrega'] ?? null;

            DB::beginTransaction();

            $manifesto->update($data);

            DB::commit();

            return $this->success($manifesto);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors'  => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();
            return $this->exception($e, '[MANIFESTO] Erro ao atualizar', [
                'id' => $id,
            ]);
        }
    }

    public function delete(int $id)
    {
        try {
            $manifesto = Manifesto::find($id);

            if (!$manifesto) {
                return $this->error('Manifesto não encontrado', 404);
            }

            $manifesto->delete();

            return $this->success(['message' => 'Manifesto excluído']);

        } catch (Throwable $e) {
            return $this->exception($e, '[MANIFESTO] Erro ao excluir', [
                'id' => $id,
            ]);
        }
    }

    private function rules(bool $create = true): array
    {
        $required = $create ? 'required|' : 'sometimes|';

        return [
            'cliente_id'      => $required . 'integer|exists:clientes,id',
            'cliente_backup'  => $required . 'string|max:255',
            'filial'          => $required . 'string|max:255',
            'destino'         => $required . 'string|max:255',
            'motorista'       => $required . 'string|max:255',
            'tipo_veiculo'    => $required . 'string|max:255',
            'placa_cavalo'    => $required . 'string|max:255',
            'observacao'      => $required . 'string|max:255',
            'valor'           => $required . 'string|max:255',
            'porcentagem'     => $required . 'string|max:255',
            'rota'            => $required . 'string|max:255',
            'tag'             => $required . 'string|max:255',
            'antt'            => $required . 'string|max:255',
            'doc_antt'        => $required . 'string|max:255',
            'responsavel'     => $required . 'string|max:255',
            'desconto'        => $required . 'string|max:255',
            'favorecido'      => $required . 'string|max:255',
            'user_id'         => $required . 'integer|exists:users,id',

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
        ];
    }
}
