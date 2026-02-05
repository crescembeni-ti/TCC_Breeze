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
        // Se NÃO estiver logado como ADMIN (Verifica explicitamente o guard e o tipo de usuário)
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Acesso restrito. Esta área é exclusiva para administradores.');
        }

        return $next($request);
    }
}
