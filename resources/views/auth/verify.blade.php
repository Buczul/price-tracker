@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">

            <div class="card border-0 rounded-0 shadow-sm">
                <div class="card-header border-0 rounded-0 fw-bold bg-primary text-white fs-5 py-3">
                    {{ __('Zweryfikuj swój adres email') }}
                </div>

                <div class="card-body p-4 lg:p-5 text-center">

                    <div class="display-1 mb-4">📧</div>

                    @if (session('resent'))
                        <div class="alert alert-success border-0 rounded-0 shadow-sm mb-4" role="alert">
                            {{ __('Nowy link weryfikacyjny został wysłany na twój adres email.') }}
                        </div>
                    @endif

                    <h5 class="fw-bold mb-3">Wymagana weryfikacja adresu e-mail</h5>

                    <p class="text-muted mb-4">
                        {{ __('Sprawdź swój email.') }}
                        {{ __('Jeżeli nie otrzymałeś linku weryfikacyjnego, kliknij przycisk poniżej, aby wysłać go ponownie.') }}
                    </p>

                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary rounded-0 fw-bold px-4 py-2 text-white">
                            Wyślij nowy link weryfikacyjny
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection