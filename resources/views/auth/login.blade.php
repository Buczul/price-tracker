@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5"> <div class="card border-0 rounded-0 shadow-sm">
                <div class="card-header border-0 rounded-0 fw-bold bg-primary text-white fs-5 py-3">
                    {{ __('Logowanie') }}
                </div>

                <div class="card-body p-4 lg:p-5">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold small text-muted">{{ __('Adres Email') }}</label>
                            <input id="email" type="email" class="form-control rounded-0 border-secondary @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold small text-muted">{{ __('Hasło') }}</label>
                            <input id="password" type="password" class="form-control rounded-0 border-secondary @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input rounded-0 border-secondary" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="remember">
                                    {{ __('Zapamietaj mnie') }}
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <button type="submit" class="btn btn-primary rounded-0 fw-bold px-5 text-white">
                                {{ __('Zaloguj') }}
                            </button>

                            @if (Route::has('password.request'))
                                <a class="text-decoration-none small text-muted fw-bold" href="{{ route('password.request') }}">
                                    {{ __('Zapomniałeś hasła?') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection