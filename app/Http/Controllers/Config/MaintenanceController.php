<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config\Maintenance;

class MaintenanceController extends Controller
{
    /**
     * Status público de manutenção
     */
    public function status()
    {
        $active = Maintenance::isActive();

        return response()->json([
            'active'  => $active,
            'message' => $active ? Maintenance::getMessage() : null,
        ]);
    }

    /**
     * Atualiza status de manutenção (ADM)
     */
    public function update(Request $request)
    {
        // 🔐 Segurança: apenas ADM
        if ($request->user()->role !== 'ADM') {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        }

        $validated = $request->validate([
            'active' => 'required|boolean',
        ]);

        Maintenance::updateStatus($validated['active']);

        return response()->json([
            'message' => $validated['active']
                ? 'Sistema colocado em manutenção'
                : 'Sistema liberado',
            'active' => $validated['active'],
        ]);
    }
}
