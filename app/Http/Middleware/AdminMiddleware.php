<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Middleware para rotas do painel administrativo
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se NÃO estiver logado como ADMIN
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Acesso restrito ao painel administrativo.');
        }

        return $next($request);
    }
}
