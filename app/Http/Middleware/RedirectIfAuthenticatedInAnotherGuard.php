<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedInAnotherGuard
{
    public function handle(Request $request, Closure $next, $guard)
    {
        // Se está logado em OUTRO guard → deve ser deslogado automaticamente
        foreach (['web', 'admin', 'analyst', 'service'] as $g) {
            if ($g !== $guard && Auth::guard($g)->check()) {
                Auth::guard($g)->logout();
            }
        }

        return $next($request);
    }
}
