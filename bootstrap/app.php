<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            // Jeśli zalogowany użytkownik jest administratorem, wyślij do panelu admina
            if (auth()->user() && auth()->user()->is_admin) {
                return route('admin.dashboard');
            }

            // W przeciwnym razie wyślij na standardową stronę użytkownika
            return '/products';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
