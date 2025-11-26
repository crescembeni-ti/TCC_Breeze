<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * 🔓 ROTAS QUE NÃO PODEM SER INTERCEPTADAS
         * (login, registro, mapa, api pública etc.)
         */
        $rotasLiberadas = [
            'login',
            'register',
            'forgot-password',
            'reset-password/*',

            // Verificação por código
            'verify-email-code',
            'verify-email-code/*',
            'verify-code',
            'verify-code/*',

            // API pública usada no mapa
            'api',
            'api/*',

            // Página pública do mapa
            '/',
            'home',
        ];

        // Ignorar rotas liberadas
        foreach ($rotasLiberadas as $rota) {
            if ($request->is($rota)) {
                $response = $next($request);
                return $this->noCache($response);
            }
        }

        // Continua a request normalmente
        $response = $next($request);

        // Remove cache de páginas protegidas
        $this->noCache($response);

        /**
         * 🚫 Usuário tenta voltar após logout
         */
        if ($this->isBackNavigationAfterLogout($request)) {

            if ($request->is('pbi-admin/*')) {
                return redirect()->route('admin.login');
            }

            if ($request->is('pbi-analista/*')) {
                return redirect()->route('analyst.login');
            }

            if ($request->is('pbi-servico/*')) {
                return redirect()->route('service.login');
            }

            // rota padrão (usuário)
            return redirect()->route('login');
        }

        return $response;
    }

    /**
     * Remove cache da página
     */
    private function noCache($response)
    {
        return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Detecta tentativa de voltar após logout
     * (considera TODOS os guards!)
     */
    private function isBackNavigationAfterLogout(Request $request): bool
    {
        return in_array($request->method(), ['GET'])
            && !auth('web')->check()
            && !auth('admin')->check()
            && !auth('analyst')->check()
            && !auth('service')->check()
            && $request->headers->get('cache-control') !== null;
    }
}
