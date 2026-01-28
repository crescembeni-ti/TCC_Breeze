<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        // Sempre remove o cache das páginas protegidas e adiciona headers de segurança
        return $this->addSecurityHeaders($this->noCache($response));
    }

    /**
     * Adiciona cabeçalhos de segurança para proteger contra Clickjacking, XSS e Sniffing
     */
    private function addSecurityHeaders($response)
    {
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        if (method_exists($response, 'header')) {
            return $response->header('X-Frame-Options', 'SAMEORIGIN')
                            ->header('X-Content-Type-Options', 'nosniff')
                            ->header('X-XSS-Protection', '1; mode=block')
                            ->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }

    /**
     * Remove cache da página de forma segura
     */
    private function noCache($response)
    {
        // 1. IGNORA DOWNLOADS
        // Se for um arquivo binário (Excel, PDF, Imagem) ou Stream, não mexe nos headers.
        // Isso corrige o erro "Call to undefined method header()" e evita downloads corrompidos.
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        // 2. Verifica se o método header existe (Responses do Laravel)
        if (method_exists($response, 'header')) {
            return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                            ->header('Pragma', 'no-cache')
                            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        // 3. Fallback seguro para outras respostas do Symfony
        // Usa a propriedade 'headers' diretamente caso o método 'header' não exista
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
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