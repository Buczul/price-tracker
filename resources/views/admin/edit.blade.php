@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 mt-4">

            <a href="{{ route('admin.index', $tabela) }}" class="btn btn-sm btn-outline-secondary mb-3 rounded-0 border-secondary">⬅ Wróć do tabeli</a>

            <div class="card shadow-sm border-0 rounded-0">
                <div class="card-header bg-warning bg-opacity-25 fw-bold text-dark fs-5 border-0 rounded-0">
                    ⚙️ Edycja surowych danych
                </div>
                <div class="card-body p-4">

                    <div class="alert alert-info small py-2 mb-4 border-0 rounded-0 shadow-sm">
                        <strong>Tabela:</strong> {{ $tabela }}<br>
                        <strong>ID Rekordu:</strong> {{ $wiersz->id }}
                    </div>

                    <form action="{{ route('admin.update', ['tabela' => $tabela, 'id' => $wiersz->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            @foreach($wiersz->getAttributes() as $klucz => $wartosc)
                                @if(!in_array($klucz, ['id', 'created_at', 'updated_at', 'email_verified_at', 'end_of_tracking_at', 'read_at']))
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold text-muted">{{ $klucz }}</label>
                                        <input type="text" name="{{ $klucz }}" class="form-control rounded-0 border-secondary" value="{{ old($klucz, $wartosc) }}">
                                    </div>
                                @else
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold text-muted">{{ $klucz }} (Zablokowane)</label>
                                        <input type="text" class="form-control bg-light text-muted rounded-0 border-secondary" value="{{ $wartosc }}" disabled>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <hr class="mt-4 mb-3 border-secondary">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning fw-bold px-5 rounded-0 border-0 shadow-sm">Zapisz w bazie danych</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection