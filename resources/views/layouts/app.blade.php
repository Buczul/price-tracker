<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@800&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        /* Styl dla nowoczesnego logotypu */
        .navbar-brand-custom {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: opacity 0.3s ease;
        }
        .navbar-brand:hover .navbar-brand-custom {
            opacity: 0.8;
        }
    </style>
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm border-bottom border-light">
            <div class="container">

                <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="{{ route('products.index') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0d6efd" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <span class="navbar-brand-custom">
                        {{ config('app.name', 'Laravel') }}
                    </span>
                </a>

                <button class="navbar-toggler rounded-0 border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <ul class="navbar-nav ms-auto align-items-center">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link fw-bold" href="{{ route('login') }}">{{ __('Zaloguj') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item ms-2">
                                    <a class="nav-link fw-bold" href="{{ route('register') }}">{{ __('Zarejestruj') }}</a>
                                </li>
                            @endif
                        @else

                            <li class="nav-item me-1 {{ Auth::user()->is_admin ? '' : 'border-end pe-3 me-3' }}">
                                <a class="nav-link fw-bold text-dark" href="{{ route('products.index') }}">
                                    🏠 Strona główna
                                </a>
                            </li>

                            @if (Auth::user()->is_admin)
                                <li class="nav-item mx-2 border-start ps-3 border-end pe-3 me-3">
                                    <a class="nav-link fw-bold text-primary" href="{{ route('admin.dashboard') }}">
                                        ⚙️ Panel admina
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item dropdown me-3">
                                <a id="notificationsDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    🔔 Powiadomienia
                                    @if (auth()->user()->unreadNotifications->count() > 0)
                                        <span class="badge bg-danger rounded-pill">{{ auth()->user()->unreadNotifications->count() }}</span>
                                    @endif
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-0"
                                    style="width: 320px; max-height: 400px; overflow-y: auto;">
                                    <h6 class="dropdown-header bg-light fw-bold border-bottom">Ostatnie obniżki</h6>

                                    @forelse(auth()->user()->unreadNotifications as $notification)
                                        <a class="dropdown-item text-wrap border-bottom py-3"
                                            href="{{ $notification->data['url'] }}" target="_blank">
                                            <small class="text-primary fw-bold d-block mb-1">{{ $notification->created_at->diffForHumans() }}</small>
                                            <span style="font-size: 0.9rem;">{{ $notification->data['message'] }}</span>
                                        </a>
                                    @empty
                                        <div class="dropdown-item text-muted small py-3 text-center">Brak nowych powiadomień.</div>
                                    @endforelse

                                    @if (auth()->user()->unreadNotifications->count() > 0)
                                        <div class="p-2 bg-light">
                                            <form action="{{ route('notifications.read') }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-0 w-100 fw-bold">Oznacz jako przeczytane</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle fw-bold" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-0" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                        ⚙️ Ustawienia profilu
                                    </a>

                                    <hr class="dropdown-divider">

                                    <a class="dropdown-item py-2 text-danger fw-bold" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        🚪 {{ __('Wyloguj') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>

</html>