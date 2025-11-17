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
         * 🔓 ROTAS SEM RESTRIÇÃO
         * Essas rotas NÃO podem sofrer redirecionamento
         * e NÃO devem ser verificadas pelo preventBack.
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

            // 🔥 API pública usada pelo mapa
            'api/*',
            'api',


            // 🔥 Página pública do mapa
            '/',
            'home',
        ];

        // Se a rota estiver liberada → processa normal sem bloqueios
        foreach ($rotasLiberadas as $rota) {
            if ($request->is($rota)) {
                $response = $next($request);
                return $this->noCache($response);
            }
        }

        // Executa a requisição normal
        $response = $next($request);

        // Remove cache de páginas protegidas
        $this->noCache($response);

        /**
         * 🚫 Tentativa de voltar após logout
         * Se o usuário NÃO está logado e a página exige login
         */
        if ($this->isBackNavigationAfterLogout($request)) {

            // Se for área administrativa
            if ($request->is('pbi-admin/*')) {
                return redirect()->route('admin.login');
            }

            // Senão, área do usuário normal
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
     * Detecta "voltar" após logout
     */
    private function isBackNavigationAfterLogout(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'POST'])
            && !auth()->check()
            && !auth('admin')->check()
            && $request->headers->get('cache-control') !== null;
    }
}
