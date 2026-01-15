<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\BaseApiController;
use App\Models\Funcionalidades\Lembrete;
use App\Models\Funcionalidades\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class LembreteController extends BaseApiController
{
    /* ───────────────────────────── *
     *  health-check                 *
     * ───────────────────────────── */
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    /* ───────────────────────────── *
     *  helper: lembretes visíveis   *
     * ───────────────────────────── */
    private function visibles(int $userId)
    {
        return Lembrete::query()
            ->with(['criador', 'visivelPara'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('visivelPara', fn ($u) => $u->where('users.id', $userId));
            })
            ->orderByDesc('last_update');
    }

    /* ───────────────────────────── *
     *  listar                       *
     * ───────────────────────────── */
    public function search(Request $r)
    {
        try {
            $userId = (int) $r->input('user_id');

            if (!$userId || !Users::whereKey($userId)->exists()) {
                return $this->error('user_id inválido', 422);
            }

            $lembretes = $this->visibles($userId)
                ->with([
                    'criador:id,name,email',
                    'visivelPara:id,name,email',
                ])
                ->get();

            return $this->success($lembretes);

        } catch (Throwable $e) {
            return $this->exception($e, '[LEMBRETE] Erro ao listar', [
                'user_id' => $r->input('user_id'),
            ]);
        }
    }

    /* ───────────────────────────── *
     *  buscar por ID                *
     * ───────────────────────────── */
    public function filter(Request $r, int $id)
    {
        try {
            $userId = (int) $r->query('user_id');

            $lembrete = $this->visibles($userId)->find($id);

            if (!$lembrete) {
                return $this->error('Lembrete não encontrado', 404);
            }

            return $this->success($lembrete);

        } catch (Throwable $e) {
            return $this->exception($e, '[LEMBRETE] Erro ao buscar', [
                'id' => $id,
                'user_id' => $r->query('user_id'),
            ]);
        }
    }

    /* ───────────────────────────── *
     *  criar                        *
     * ───────────────────────────── */
    public function cad(Request $r)
    {
        try {
            $val = $r->validate([
                'titulo'          => 'required|string|max:255',
                'lembrete'        => 'required|string',
                'cor'             => 'nullable|string|max:50',
                'user_id'         => 'required|exists:users,id',
                'visivel_users'   => 'nullable|array',
                'visivel_users.*' => 'integer|exists:users,id',
            ]);

            DB::beginTransaction();

            $lembrete = Lembrete::create([
                'titulo'   => $val['titulo'],
                'lembrete' => $val['lembrete'],
                'cor'      => $val['cor'] ?? null,
                'user_id'  => $val['user_id'],
                'feito'    => false,
            ]);

            if (!empty($val['visivel_users'])) {
                $lembrete->visivelPara()->sync($val['visivel_users']);
            }

            DB::commit();

            return $this->success(
                $lembrete->load(['criador', 'visivelPara']),
                201
            );

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();
            return $this->exception($e, '[LEMBRETE] Erro ao criar', [
                'payload' => $r->all(),
            ]);
        }
    }

    /* ───────────────────────────── *
     *  editar                       *
     * ───────────────────────────── */
    public function edit(Request $r, int $id)
    {
        try {
            $lembrete = Lembrete::find($id);

            if (!$lembrete) {
                return $this->error('Lembrete não encontrado', 404);
            }

            $val = $r->validate([
                'titulo'          => 'required|string|max:255',
                'lembrete'        => 'required|string',
                'cor'             => 'nullable|string|max:50',
                'visivel_users'   => 'nullable|array',
                'visivel_users.*' => 'integer|exists:users,id',
            ]);

            DB::beginTransaction();

            $lembrete->update($val);

            if (array_key_exists('visivel_users', $val)) {
                $lembrete->visivelPara()->sync($val['visivel_users'] ?? []);
            }

            DB::commit();

            return $this->success(
                $lembrete->load(['criador', 'visivelPara'])
            );

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            DB::rollBack();
            return $this->exception($e, '[LEMBRETE] Erro ao editar', ['id' => $id]);
        }
    }

    /* ───────────────────────────── *
     *  marcar feito                 *
     * ───────────────────────────── */
    public function done(Request $r, int $id)
    {
        try {
            $val = $r->validate([
                'feito' => 'required|boolean',
            ]);

            $lembrete = Lembrete::find($id);

            if (!$lembrete) {
                return $this->error('Lembrete não encontrado', 404);
            }

            $lembrete->update(['feito' => $val['feito']]);

            return $this->success(['feito' => $lembrete->feito]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            return $this->exception($e, '[LEMBRETE] Erro ao marcar feito', [
                'id' => $id,
            ]);
        }
    }

    /* ───────────────────────────── *
     *  excluir                      *
     * ───────────────────────────── */
    public function delete(int $id)
    {
        try {
            $lembrete = Lembrete::find($id);

            if (!$lembrete) {
                return $this->error('Lembrete não encontrado', 404);
            }

            DB::beginTransaction();

            $lembrete->visivelPara()->detach();
            $lembrete->delete();

            DB::commit();

            return $this->success(['message' => 'Lembrete excluído']);

        } catch (Throwable $e) {
            DB::rollBack();
            return $this->exception($e, '[LEMBRETE] Erro ao excluir', [
                'id' => $id,
            ]);
        }
    }
}
