<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Impede que páginas protegidas fiquem armazenadas no cache
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        // VERIFICA SE O USUÁRIO VOLTOU NA SETINHA APÓS LOGOUT
        if ($this->isBackNavigationAfterLogout($request)) {

            // ➤ SE O ADMIN estava logado → redirecionar para login admin
            if (!auth('admin')->check() && $request->is('pbi-admin/*')) {
                return redirect()->route('admin.login');
            }

            // ➤ SE O USUÁRIO comum estava logado → redirecionar para login comum
            if (!auth()->check() && !$request->is('pbi-admin/*')) {
                return redirect()->route('login');
            }
        }

        return $response;
    }

    private function isBackNavigationAfterLogout(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'POST'])
            && !auth()->check()
            && !auth('admin')->check()
            && $request->headers->get('cache-control') !== null;
    }
}
