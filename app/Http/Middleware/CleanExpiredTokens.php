<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;

class CleanExpiredTokens
{
    public function handle(Request $request, Closure $next)
    {
        // Limpar tokens expirados periodicamente
        if (rand(1, 100) <= 10) { // 10% de chance a cada request
            PersonalAccessToken::where('expires_at', '<', now())->delete();
        }

        return $next($request);
    }
}