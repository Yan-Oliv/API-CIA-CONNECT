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
        $role     = (string) $request->input('user_role');
        $context  = (string) $request->input('context');
        $filialId = (string) $request->input('filial_id');
    
        $onlyUnread = $request->boolean('only_unread', true);
    
        $query = Notificacoes::query();
    
        if ($onlyUnread) {
            $query->where('read', false);
        }
    
        $query->where('type', '!=', 'logs')
              ->where(function ($q) use ($userId, $role, $filialId, $context) {
    
                  // SISTEMA → criador
                  $q->where(function ($q) use ($userId) {
                      $q->where('type', 'sistema')
                        ->where('created_by', $userId);
                  });
    
                  // ATUALIZAÇÃO → contexto + role OU filial
                  $q->orWhere(function ($q) use ($role, $filialId, $context) {
                      $q->where('type', 'atualizacao')
                        ->where('context', $context)
                        ->where(function ($q) use ($role, $filialId) {
                            $q->whereJsonContains('visible_to_roles', $role)
                              ->orWhereJsonContains('visible_to_filial', $filialId);
                        });
                  });
    
                  // URGENTE / IMPORTANTE / ATENÇÃO
                  $q->orWhere(function ($q) use ($userId) {
                      $q->whereIn('type', ['urgente', 'importante', 'atencao'])
                        ->whereJsonContains('visible_to_users', $userId);
                  });
              });
    
        // ADM vê tudo (respeitando contexto)
        if ($role === 'ADM') {
            $query = Notificacoes::query()
                ->where('context', $context)
                ->when($onlyUnread, fn ($q) => $q->where('read', false));
        }

    
        return response()->json(
            $query->orderByDesc('timestamp')->get(),
            200
        );
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
        $data['visible_to_roles'] = $data['visible_to_roles']
            ? array_values($data['visible_to_roles'])
            : null;


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
        $userId  = (int) $request->input('user_id');
        $context = (string) $request->input('context');
    
        Notificacoes::where('context', $context)
            ->where(function ($q) use ($userId) {
                $q->whereJsonContains('visible_to_users', $userId)
                  ->orWhere('created_by', $userId);
            })
            ->update(['read' => true]);
    
        return response()->json(['message' => 'Todas lidas'], 200);
    }

}
