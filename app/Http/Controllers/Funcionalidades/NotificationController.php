<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionalidades\Notificacoes;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Retorna “pong” para health‑check.
     */
    public function index()
    {
        return response()->json(['status' => 'OK']);
    }

    /**
     * Busca todas as notificações VISÍVEIS ao usuário logado.
     * Reproduz a regra do Flutter:
     *  - ADM vê tudo
     *  - type = logs  → só ADM
     *  - type = sistema → criador + ADM
     *  - type = atualizacao → mesmo context E (mesmo role OU mesma filial)
     *  - type = urgente/importante/atencao → se user_id estiver em visible_to_users
     */
    public function search(Request $request)
    {
        $userId   = (int) $request->input('user_id');
        $userRole = (string) $request->input('user_role');
        $context  = (string) $request->input('context');
        $filialId = (string) $request->input('filial_id');

        if (!$userId || !$userRole) {
            return response()->json(['error' => 'Parâmetros inválidos'], 422);
        }

        // ADM vê tudo
        if ($userRole === 'ADM') {
            return response()->json(
                Notificacoes::orderByDesc('timestamp')->get(),
                200
            );
        }

        $query = Notificacoes::query()
            ->where(function ($q) use ($userId, $userRole, $filialId, $context) {

                // SISTEMA → criador
                $q->where(function ($q) use ($userId) {
                    $q->where('type', 'sistema')
                    ->where('created_by', $userId);
                })

                // ATUALIZACAO → mesmo role OU mesma filial
                ->orWhere(function ($q) use ($userRole, $filialId) {
                    $q->where('type', 'atualizacao')
                    ->where(function ($q) use ($userRole, $filialId) {
                        $q->whereJsonContains('visible_to_roles', $userRole)
                            ->orWhereJsonContains('visible_to_filial', $filialId);
                    });
                })

                // URGENTE | IMPORTANTE | ATENCAO → usuário listado
                ->orWhere(function ($q) use ($userId) {
                    $q->whereIn('type', ['urgente', 'importante', 'atencao'])
                    ->whereJsonContains('visible_to_users', $userId);
                });
            })
            ->orderByDesc('timestamp');

        return response()->json($query->get(), 200);
    }


    /**
     * Cria nova notificação vinda do seu próprio backend (ex.: jobs, webhooks).
     * Tipicamente o Flutter cria pelo próprio app; este endpoint é útil para
     * processos internos do servidor.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'message'           => 'required|string',
            'type'              => 'required|string',
            'context'           => 'required|string',
            'created_by'        => 'nullable|integer|exists:users,id',
            'visible_to_roles'  => 'nullable|array',
            'visible_to_users'  => 'nullable|array',
            'visible_to_filial' => 'nullable|array',
        ]);

        $data['read']      = false;
        $data['timestamp'] = now();

        $noty = Notificacoes::create($data);

        return response()->json([
            'message' => 'Notificação criada',
            'data'    => $noty,
        ], 201);
    }

    /**
     * Marca todas as notificações do usuário como lidas.
     */
    public function markAllRead(Request $request)
    {
        $userId = (int) $request->input('user_id');

        Notificacoes::whereJsonContains('visible_to_users', $userId)
            ->orWhere('created_by', $userId)
            ->update(['read' => true]);

        return response()->json(['message' => 'Todas lidas'], 200);
    }
}
