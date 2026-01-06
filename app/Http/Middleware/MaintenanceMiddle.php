<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Config\Maintenance;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        // Rotas que nunca entram em maintenance
        $except = [
            'login',
            'log',
            'validate',
            'config/maintenance',
        ];

        if ($request->is($except)) {
            return $next($request);
        }

        if (!Maintenance::isActive()) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->role === 'ADM') {
            return $next($request);
        }

        return response()->json([
            'maintenance' => true,
            'message' => Maintenance::getMessage(),
        ], 503);
    }
}
