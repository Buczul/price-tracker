<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    public function redirectTo()
{
    // Sprawdzamy, czy zalogowany użytkownik ma flagę administratora
    if (auth()->user()->is_admin) {
        // Przekierowanie do strony głównej panelu admina (zdefiniowanej w routes/web.php)
        return route('admin.dashboard');
    }

    // Ścieżka dla zwykłego użytkownika (np. lista śledzonych produktów)
    return '/products';
}

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
