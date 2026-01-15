<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\BaseApiController;
use App\Models\Referencias\Carroceria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CarroceriaController extends BaseApiController
{
    public function index()
    {
        return $this->success(null);
    }

    public function search()
    {
        try {
            $carrocerias = Carroceria::orderBy('id')->get();

            Log::info('[Carroceria] Lista carregada', [
                'total' => $carrocerias->count(),
            ]);

            return $this->success($carrocerias);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[Carroceria] Erro ao buscar lista',
                $this->baseContext()
            );
        }
    }

    public function filter(int $id)
    {
        try {
            $carroceria = Carroceria::find($id);

            if (!$carroceria) {
                return $this->error('Carroceria não encontrada', 404);
            }

            return $this->success($carroceria);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[Carroceria] Erro ao filtrar',
                array_merge($this->baseContext(), ['id' => $id])
            );
        }
    }

    public function cad(Request $request)
    {
        $data = $request->validate([
            'nome'    => 'required|string|max:255',
            'tipo'    => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $carroceria = Carroceria::create($data);

            Log::info('[Carroceria] Criada', [
                'id' => $carroceria->id,
                'user_id' => $data['user_id'],
            ]);

            return $this->success($carroceria, 201);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[Carroceria] Erro ao criar',
                array_merge($this->baseContext(), $data)
            );
        }
    }

    public function edit(Request $request, int $id)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
        ]);

        try {
            $carroceria = Carroceria::find($id);

            if (!$carroceria) {
                return $this->error('Carroceria não encontrada', 404);
            }

            $carroceria->update($data);

            Log::info('[Carroceria] Atualizada', [
                'id' => $id,
            ]);

            return $this->success($carroceria);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[Carroceria] Erro ao atualizar',
                array_merge($this->baseContext(), ['id' => $id])
            );
        }
    }

    public function delete(int $id)
    {
        try {
            $carroceria = Carroceria::find($id);

            if (!$carroceria) {
                return $this->error('Carroceria não encontrada', 404);
            }

            $carroceria->delete();

            Log::warning('[Carroceria] Excluída', [
                'id' => $id,
            ]);

            return $this->success(['message' => 'Excluída com sucesso']);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[Carroceria] Erro ao excluir',
                array_merge($this->baseContext(), ['id' => $id])
            );
        }
    }

    public function filterCarrocerias(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ]);

        try {
            $carrocerias = Carroceria::whereIn('id', $data['ids'])
                ->orderBy('id')
                ->get();

            return $this->success($carrocerias);
        } catch (Throwable $e) {
            return $this->exception(
                $e,
                '[Carroceria] Erro ao filtrar múltiplos',
                array_merge($this->baseContext(), $data)
            );
        }
    }
}