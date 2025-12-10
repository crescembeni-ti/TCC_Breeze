<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedInAnotherGuard
{
    public function handle(Request $request, Closure $next, $guard)
    {
        foreach (['web', 'admin', 'analyst', 'service'] as $g) {

            // Se estiver autenticado em outro guard → desloga para evitar conflito
            if ($g !== $guard && Auth::guard($g)->check()) {
                Auth::guard($g)->logout();
            }
        }

        // Se JÁ estiver logado no guard correto, apenas segue
        if (Auth::guard($guard)->check()) {
            return $next($request);
        }

        return $next($request);
    }
}
