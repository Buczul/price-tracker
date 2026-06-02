@extends('layouts.app')

@section('content')
<style>
    /* Utrzymanie kanciastego stylu również na stronie głównej */
    .card, .btn {
        border-radius: 0 !important;
    }
    .card {
        border: none !important;
    }
    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important;
    }
</style>

<div class="container-xl mt-5 pt-5">

    {{-- Główny nagłówek i sekcja wezwania do akcji (Call to Action) --}}
    <div class="row justify-content-center text-center mb-5 pb-4">
        <div class="col-lg-8">
            <h1 class="display-4 fw-bold text-dark mb-3">{{ config('app.name', 'Tracker Cen') }}</h1>
            <h3 class="text-secondary fw-normal mb-5">
                Śledź ceny pożądanych produktów i kupuj zawsze w najlepszym momencie.
            </h3>

            {{-- Przyciski nawigacyjne zależne od statusu logowania --}}
            <div class="d-flex justify-content-center gap-3">
                @auth
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg shadow-sm px-5 fw-bold">
                        Przejdź do swojego panelu
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg shadow-sm px-5 fw-bold">
                            Zaloguj się
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-outline-dark btn-lg shadow-sm px-5 fw-bold">
                            Załóż darmowe konto
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    {{-- Sekcja informacyjna z kartami (Cechy aplikacji) --}}
    <div class="row g-4 mt-4">

        {{-- Karta: Ustal cel --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100 bg-white">
                <div class="card-body text-center p-5">
                    <div class="display-4 mb-3">🎯</div>
                    <h5 class="fw-bold mb-3">Ustal cel</h5>
                    <p class="text-muted small mb-0">
                        Dodaj produkt, który Cię interesuje i ustaw wymarzoną cenę docelową.
                    </p>
                </div>
            </div>
        </div>

        {{-- Karta: Automatyzacja --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100 bg-white">
                <div class="card-body text-center p-5">
                    <div class="display-4 mb-3">🤖</div>
                    <h5 class="fw-bold mb-3">Automatyzacja</h5>
                    <p class="text-muted small mb-0">
                        Nasz system o 2:00 w nocy codziennie sprawdza ceny w podpiętych przez Ciebie sklepach.
                    </p>
                </div>
            </div>
        </div>

        {{-- Karta: Powiadomienia --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100 bg-white">
                <div class="card-body text-center p-5">
                    <div class="display-4 mb-3">🔔</div>
                    <h5 class="fw-bold mb-3">Powiadomienia</h5>
                    <p class="text-muted small mb-0">
                        Otrzymaj e-mail natychmiast, gdy tylko cena spadnie do oczekiwanego poziomu.
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection