<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jeśli użytkownik jest zalogowany I jest administratorem, puść go dalej
        if (auth()->check() && auth()->user()->is_admin) {
            return $next($request);
        }

        // W przeciwnym razie zablokuj dostęp
        abort(403, 'Nie masz uprawnień do przeglądania tej strony.');
    }
}