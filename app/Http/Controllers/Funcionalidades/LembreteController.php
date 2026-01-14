<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use App\Models\Funcionalidades\Lembrete;
use App\Models\Funcionalidades\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LembreteController extends Controller
{
    /* ─────────────────────────────────────────── *
     *  HELPER: lembretes visíveis ao usuário      *
     * ─────────────────────────────────────────── */
    private function visibles(int $userId)
    {
        return Lembrete::query()
            ->with(['criador', 'visivelPara'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('visivelPara', function ($u) use ($userId) {
                      $u->where('users.id', $userId);
                  });
            })
            ->orderByDesc('last_update');
    }

    /* ───────────────────────────── *
     *  health-check                 *
     * ───────────────────────────── */
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    /* ───────────────────────────── *
     *  listar lembretes             *
     * ───────────────────────────── */
    public function search(Request $r)
    {
        $userId = (int) $r->input('user_id');

        if (!$userId || !Users::whereKey($userId)->exists()) {
            return response()->json(['error' => 'user_id inválido'], 422);
        }

        $lembretes = $this->visibles($userId)
            ->with([
                'criador:id,name,email',
                'visivelPara:id,name,email',
            ])
            ->get();

        return response()->json($lembretes, 200);
    }

    /* ───────────────────────────── *
     *  buscar por ID                *
     * ───────────────────────────── */
    public function filter(Request $r, int $id)
    {
        $userId = (int) $r->query('user_id');

        $lembrete = $this->visibles($userId)->find($id);

        if (!$lembrete) {
            return response()->json(['error' => 'Lembrete não encontrado'], 404);
        }

        return response()->json($lembrete, 200);
    }

    /* ───────────────────────────── *
     *  criar lembrete               *
     * ───────────────────────────── */
    public function cad(Request $r)
    {
        $val = $r->validate([
            'titulo'          => 'required|string|max:255',
            'lembrete'        => 'required|string',
            'cor'             => 'nullable|string|max:50',
            'user_id'         => 'required|integer|exists:users,id',
            'visivel_users'   => 'nullable|array',
            'visivel_users.*' => 'integer|exists:users,id',
        ]);

	$lembrete = null;

        DB::transaction(function () use (&$lembrete, $val) {
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
        });

        return response()->json(
            $lembrete->load(['criador', 'visivelPara']),
            201
        );
    }

    /* ───────────────────────────── *
     *  editar lembrete              *
     * ───────────────────────────── */
    public function edit(Request $r, int $id)
    {
        $lembrete = Lembrete::find($id);

        if (!$lembrete) {
            return response()->json(['error' => 'Lembrete não encontrado'], 404);
        }

        $val = $r->validate([
            'titulo'          => 'required|string|max:255',
            'lembrete'        => 'required|string',
            'cor'             => 'nullable|string|max:50',
            'visivel_users'   => 'nullable|array',
            'visivel_users.*' => 'integer|exists:users,id',
        ]);

        DB::transaction(function () use ($lembrete, $val) {
            $lembrete->update($val);

            if (array_key_exists('visivel_users', $val)) {
                $lembrete->visivelPara()->sync($val['visivel_users'] ?? []);
            }
        });

        return response()->json(
            $lembrete->load(['criador', 'visivelPara']),
            200
        );
    }

    /* ───────────────────────────── *
     *  marcar feito                 *
     * ───────────────────────────── */
    public function done(Request $r, int $id)
    {
        $val = $r->validate([
            'feito' => 'required|boolean',
        ]);

        $lembrete = Lembrete::find($id);

        if (!$lembrete) {
            return response()->json(['error' => 'Lembrete não encontrado'], 404);
        }

        $lembrete->update(['feito' => $val['feito']]);

        return response()->json(['feito' => $lembrete->feito], 200);
    }

    /* ───────────────────────────── *
     *  excluir                      *
     * ───────────────────────────── */
    public function delete(int $id)
    {
        $lembrete = Lembrete::find($id);

        if (!$lembrete) {
            return response()->json(['error' => 'Lembrete não encontrado'], 404);
        }

        DB::transaction(function () use ($lembrete) {
            $lembrete->visivelPara()->detach();
            $lembrete->delete();
        });

        return response()->json(['message' => 'Lembrete excluído'], 200);
    }
}
