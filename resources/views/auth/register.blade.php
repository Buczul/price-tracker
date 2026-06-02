@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            {{-- Główna Karta Rejestracyjna --}}
            <div class="card border-0 rounded-0 shadow-sm">

                <div class="card-header border-0 rounded-0 fw-bold bg-primary text-white fs-5 py-3">
                    {{ __('Rejestracja') }}
                </div>

                <div class="card-body p-4 lg:p-5">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        {{-- Wprowadzanie nazwy użytkownika --}}
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold small text-muted">{{ __('Nazwa użytkownika') }}</label>
                            <input id="name" type="text" class="form-control rounded-0 border-secondary @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Wejście e-mail --}}
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold small text-muted">{{ __('Adres email') }}</label>
                            <input id="email" type="email" class="form-control rounded-0 border-secondary @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Wprowadzanie hasła --}}
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold small text-muted">{{ __('Hasło') }}</label>
                            <input id="password" type="password" class="form-control rounded-0 border-secondary @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Potwierdź wprowadzenie hasła --}}
                        <div class="mb-4">
                            <label for="password-confirm" class="form-label fw-bold small text-muted">{{ __('Potwierdź hasło') }}</label>
                            <input id="password-confirm" type="password" class="form-control rounded-0 border-secondary" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        {{-- Przycisk Prześlij --}}
                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary rounded-0 fw-bold py-2 text-white">
                                {{ __('Zarejestruj') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            {{-- Link do logowania dla istniejących użytkowników --}}
            <div class="text-center mt-4">
                <span class="text-muted small">Masz już konto?</span>
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold small">Zaloguj się</a>
            </div>

        </div>
    </div>
</div>
@endsection