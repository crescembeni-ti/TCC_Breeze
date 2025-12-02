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
         * Rotas que não devem ser bloqueadas
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

            // API pública
            'api',
            'api/*',

            // Página pública
            '/',
            'home',
        ];

        // Se rota for liberada → só aplica noCache e continua
        foreach ($rotasLiberadas as $rota) {
            if ($request->is($rota)) {
                $response = $next($request);
                return $this->noCache($response);
            }
        }

        /**
         * Detecta tentativa de voltar após logout
         * (SEM executar o controller antes!)
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

            return redirect()->route('login');
        }

        /**
         * Agora sim executa a request real
         */
        $response = $next($request);

        // Sempre remove o cache das páginas protegidas
        return $this->noCache($response);
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
     */
    private function isBackNavigationAfterLogout(Request $request): bool
    {
        return $request->isMethod('GET')
            && !$this->isAnyGuardLoggedIn()
            && $request->headers->get('cache-control') !== null;
    }

    /**
     * Verifica TODOS os guards
     */
    private function isAnyGuardLoggedIn(): bool
    {
        return auth('web')->check()
            || auth('admin')->check()
            || auth('analyst')->check()
            || auth('service')->check();
    }
}
