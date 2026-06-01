@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7"> <div class="card border-0 rounded-0 shadow-sm">
                <div class="card-header border-0 rounded-0 fw-bold bg-primary text-white fs-5 py-3">
                    Pomyślnie zalogowano
                </div>

                <div class="card-body p-4 lg:p-5 text-center">

                    @if (session('status'))
                        <div class="alert alert-success border-0 rounded-0 shadow-sm mb-4" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="display-1 mb-4">👋</div>

                    <h4 class="fw-bold mb-3">Witaj, {{ Auth::user()->name }}!</h4>

                    <p class="text-muted mb-4">
                        Zostałeś poprawnie zalogowany do systemu. Twój radar cenowy jest gotowy do działania!
                    </p>

                    <a href="{{ route('products.index') }}" class="btn btn-primary rounded-0 fw-bold px-5 py-2 text-white shadow-sm">
                        Przejdź do Twojego panelu
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection