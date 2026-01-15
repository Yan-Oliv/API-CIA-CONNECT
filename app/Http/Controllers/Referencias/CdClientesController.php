<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\BaseApiController;
use App\Models\Referencias\CdCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CdClientesController extends BaseApiController
{
    public function index()
    {
        return $this->success(null);
    }

    public function search()
    {
        try {
            $cds = CdCliente::orderBy('id')->get();

            Log::info('[CD] Lista carregada', [
                'total' => $cds->count(),
                'user_id' => auth()->id(),
            ]);

            return $this->success($cds);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[CD] Erro ao listar CDs',
                $this->baseContext()
            );
        }
    }

    public function filter(int $id)
    {
        try {
            $cd = CdCliente::find($id);

            if (!$cd) {
                return $this->error('CD não encontrado', 404);
            }

            return $this->success($cd);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[CD] Erro ao buscar CD',
                array_merge($this->baseContext(), ['id' => $id])
            );
        }
    }

    public function cad(Request $request)
    {
        $data = $request->validate([
            'cliente_id'  => 'required|integer|exists:clientes,id',
            'nome_filial' => 'required|string|max:255',
            'cidade'      => 'required|string|max:100',
            'estado'      => 'required|string|size:2',
        ]);

        try {
            $cd = CdCliente::create($data);

            Log::info('[CD] Criado', [
                'id' => $cd->id,
                'user_id' => auth()->id(),
            ]);

            return $this->success($cd, 201);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[CD] Erro ao criar CD',
                array_merge($this->baseContext(), $data)
            );
        }
    }

    public function edit(Request $request, int $id)
    {
        $data = $request->validate([
            'nome_filial' => 'required|string|max:255',
            'cidade'      => 'required|string|max:100',
            'estado'      => 'required|string|size:2',
        ]);

        try {
            $cd = CdCliente::find($id);

            if (!$cd) {
                return $this->error('CD não encontrado', 404);
            }

            $cd->update($data);

            Log::info('[CD] Atualizado', [
                'id' => $id,
                'user_id' => auth()->id(),
            ]);

            return $this->success($cd);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[CD] Erro ao atualizar CD',
                array_merge($this->baseContext(), ['id' => $id])
            );
        }
    }

    public function delete(int $id)
    {
        try {
            $cd = CdCliente::find($id);

            if (!$cd) {
                return $this->error('CD não encontrado', 404);
            }

            $cd->delete();

            Log::warning('[CD] Excluído', [
                'id' => $id,
                'user_id' => auth()->id(),
            ]);

            return $this->success(['message' => 'CD excluído com sucesso']);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[CD] Erro ao excluir CD',
                array_merge($this->baseContext(), ['id' => $id])
            );
        }
    }

    public function filterCds(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        try {
            $cds = CdCliente::whereIn('id', $data['ids'])
                ->orderBy('id')
                ->get();

            return $this->success($cds);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[CD] Erro ao filtrar múltiplos',
                array_merge($this->baseContext(), $data)
            );
        }
    }
}