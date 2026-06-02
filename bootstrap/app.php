<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\AdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Zarejestruj aliasy pośredniczące tras
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);

        // Dostosuj lokalizację przekierowania dla uwierzytelnionych użytkowników
        $middleware->redirectUsersTo(function (Request $request) {

            // Jeśli zalogowany użytkownik jest administratorem, przekieruj do panelu administratora
            if (auth()->user() && auth()->user()->is_admin) {
                return route('admin.dashboard');
            }

            // W przeciwnym razie nastąpi przekierowanie do standardowego panelu użytkownika (lista produktów)
            return '/products';
        });

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();