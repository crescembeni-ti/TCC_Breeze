<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado e é administrador
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            // Redireciona para a página inicial se não for admin
            return redirect('/')->with('error', 'Acesso negado. Você não tem permissão para acessar esta página.');
        }

        return $next($request);
    }
}
