<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware weryfikujący uprawnienia administratora.
 * Zabezpiecza wybrane trasy przed nieautoryzowanym dostępem.
 */
class AdminMiddleware
{
    /**
     * Przetwarza nadchodzące żądanie HTTP.
     *
     * @param Request $request Obiekt żądania
     * @param Closure $next Funkcja przekazująca żądanie do kolejnego etapu
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sprawdzenie, czy użytkownik jest uwierzytelniony i posiada flagę administratora
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        // Przerwanie żądania z kodem błędu 403 (Forbidden), jeśli warunek nie został spełniony
        abort(403, 'Nie masz uprawnień do przeglądania tej strony.');
    }
}