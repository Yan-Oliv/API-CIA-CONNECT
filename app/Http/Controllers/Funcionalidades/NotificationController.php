<?php

namespace App\Http\Controllers\Funcionalidades;

use App\Http\Controllers\BaseApiController;
use App\Models\Funcionalidades\Notificacoes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class NotificationController extends BaseApiController
{
    public function index()
    {
        return $this->success(['status' => 'OK']);
    }

    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id'     => 'required|integer',
                'user_role'   => 'required|string',
                'context'     => 'required|string',
                'filial_id'   => 'nullable|string',
                'only_unread' => 'boolean',
            ]);

            $query = Notificacoes::query();

            if ($validated['only_unread'] ?? true) {
                $query->where('read', false);
            }

            if ($validated['user_role'] === 'ADM') {
                if ($validated['context'] !== 'dashboard') {
                    $query->where(function ($q) use ($validated) {
                        $q->where('context', $validated['context'])
                          ->orWhereNull('context');
                    });
                }

                return $this->success(
                    $query->orderByDesc('timestamp')->get()
                );
            }

            $query->where('type', '!=', 'logs')
                ->where(function ($q) use ($validated) {

                    $q->where('created_by', $validated['user_id'])

                      ->orWhere(function ($q2) use ($validated) {
                          $q2->where('type', 'sistema')
                             ->where('created_by', $validated['user_id']);
                      })

                      ->orWhere(function ($q2) use ($validated) {
                          $q2->where('type', 'atualizacao')
                             ->when(
                                 $validated['context'] !== 'dashboard',
                                 fn ($qq) => $qq->where('context', $validated['context'])
                             )
                             ->where(function ($qq) use ($validated) {
                                 $qq->whereJsonContains('visible_to_roles', $validated['user_role'])
                                    ->orWhereJsonContains('visible_to_filial', $validated['filial_id'])
                                    ->orWhere(function ($q3) {
                                        $q3->whereNull('visible_to_roles')
                                           ->whereNull('visible_to_filial')
                                           ->whereNull('visible_to_users');
                                    });
                             });
                      })

                      ->orWhere(function ($q2) use ($validated) {
                          $q2->whereIn('type', ['urgente', 'importante', 'atencao'])
                             ->whereJsonContains('visible_to_users', $validated['user_id']);
                      });
                });

            return $this->success(
                $query->orderByDesc('timestamp')->get()
            );

        } catch (ValidationException $e) {
            return $this->error('Parâmetros inválidos', 422, [
                'errors' => $e->errors(),
            ]);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao buscar notificações', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
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

            Log::info('[NOTIFICATION CREATED]', [
                'id' => $noty->id,
                'type' => $noty->type,
            ]);

            return $this->success($noty, 201);

        } catch (ValidationException $e) {
            return $this->error('Dados inválidos', 422, [
                'errors' => $e->errors(),
            ]);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao criar notificação', [
                'payload' => $request->all(),
            ]);
        }
    }

    public function markAllRead(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id'   => 'required|integer',
                'context'   => 'required|string',
                'user_role' => 'required|string',
                'filial_id' => 'nullable|string',
            ]);

            $query = Notificacoes::query()
                ->where('context', $validated['context']);

            if ($validated['user_role'] !== 'ADM') {
                $query->where('type', '!=', 'logs')
                      ->where(function ($q) use ($validated) {
                          $q->where('created_by', $validated['user_id'])
                            ->orWhereJsonContains('visible_to_users', $validated['user_id'])
                            ->orWhereJsonContains('visible_to_roles', $validated['user_role'])
                            ->orWhereJsonContains('visible_to_filial', $validated['filial_id']);
                      });
            }

            $query->update(['read' => true]);

            return $this->success(['message' => 'Notificações marcadas como lidas']);

        } catch (ValidationException $e) {
            return $this->error('Parâmetros inválidos', 422, [
                'errors' => $e->errors(),
            ]);
        } catch (Throwable $e) {
            return $this->exception($e, 'Erro ao marcar notificações', [
                'payload' => $request->all(),
            ]);
        }
    }
}