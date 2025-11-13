<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        // 🔥 LISTA DE ROTAS QUE NÃO PODEM SER BLOQUEADAS
        // (rotas acessíveis mesmo quando deslogado)
        $rotasLiberadas = [
            'login',
            'register',
            'forgot-password',
            'reset-password/*',

            // Rotas de verificação por código
            'verify-email-code',
            'verify-email-code/*',
            'verify-code',
            'verify-code/*',
        ];

        // 🔥 Se a rota for liberada → não aplica nenhuma verificação de logout
        foreach ($rotasLiberadas as $rota) {
            if ($request->is($rota)) {
                $response = $next($request);
                return $this->noCache($response);
            }
        }

        // Executa a requisição
        $response = $next($request);

        // Impede cache em páginas protegidas
        $this->noCache($response);

        // 🔥 Verifica tentativa de voltar após logout
        if ($this->isBackNavigationAfterLogout($request)) {

            // Se era admin → login admin
            if ($request->is('pbi-admin/*')) {
                return redirect()->route('admin.login');
            }

            // Se era usuário comum → login normal
            return redirect()->route('login');
        }

        return $response;
    }

    private function noCache($response)
    {
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
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
