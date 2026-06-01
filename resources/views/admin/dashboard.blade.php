@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h2 class="mb-0">📊 Panel Administratora</h2>
                <div class="nav nav-pills">
                    <a class="nav-link active me-2" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="nav-link text-secondary fw-bold" href="{{ route('admin.index', 'produkty') }}">Zarządzanie Bazą Danych</a>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white h-100 shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h6 class="text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">Użytkownicy</h6>
                            <h2 class="fw-bold mb-0">{{ $stats['users'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white h-100 shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h6 class="text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">Produkty</h6>
                            <h2 class="fw-bold mb-0">{{ $stats['products'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-dark h-100 shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h6 class="text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">Śledzone sklepy</h6>
                            <h2 class="fw-bold mb-0">{{ $stats['urls'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-dark h-100 shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h6 class="text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">Zapisane ceny</h6>
                            <h2 class="fw-bold mb-0">{{ $stats['histories'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header fw-bold bg-light">🔥 Najpopularniejsze domeny</div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($topStores as $store)
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <span class="text-secondary font-monospace">{{ $store->store_name }}</span>
                                        <span class="badge bg-primary rounded-pill">{{ $store->total }} szt.</span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted small py-3">Brak danych o sklepach.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-7 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header fw-bold bg-light">🆕 Ostatnio dodane produkty</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 small align-middle">
                                    <tbody>
                                        @forelse($recentProducts as $product)
                                            <tr>
                                                <td class="py-3 ps-3">
                                                    <strong>{{ $product->name }}</strong>
                                                    <span class="text-muted d-block" style="font-size: 0.75rem;">
                                                        Dodano: {{ $product->created_at->format('d.m.Y H:i') }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <span class="badge bg-light text-dark border">
                                                        👤 {{ $product->user->name ?? 'Usunięty' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-muted py-3 ps-3">Brak nowo dodanych produktów.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection