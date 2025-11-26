<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // === Alias de Middleware Personalizados ===
        $middleware->alias([
            'admin'         => \App\Http\Middleware\AdminMiddleware::class,
            'preventBack'   => \App\Http\Middleware\PreventBackHistory::class,

            // Middleware ESSENCIAL para multi-auth
            // Garante que ao acessar outra área, guards incorretos são deslogados
            'guard.only'    => \App\Http\Middleware\RedirectIfAuthenticatedInAnotherGuard::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
