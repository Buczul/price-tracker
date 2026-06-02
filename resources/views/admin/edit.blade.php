@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 mt-4">

            {{-- Przycisk powrotu do indeksu tabeli --}}
            <a href="{{ route('admin.index', $table) }}" class="btn btn-sm btn-outline-secondary mb-3 rounded-0 border-secondary">⬅ Wróć do tabeli</a>

            {{-- Karta główna do edycji danych surowych --}}
            <div class="card shadow-sm border-0 rounded-0">
                <div class="card-header bg-warning bg-opacity-25 fw-bold text-dark fs-5 border-0 rounded-0">
                    ⚙️ Edycja surowych danych
                </div>
                <div class="card-body p-4">

                    {{-- Alert informacyjny o aktualnie edytowanym rekordzie --}}
                    <div class="alert alert-info small py-2 mb-4 border-0 rounded-0 shadow-sm">
                        <strong>Tabela:</strong> {{ $table }}<br>
                        <strong>ID Rekordu:</strong> {{ $row->id }}
                    </div>

                    {{-- Formularz aktualizacji rekordu --}}
                    <form action="{{ route('admin.update', ['table' => $table, 'id' => $row->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Dynamiczne generowanie pól na podstawie atrybutów rekordu --}}
                            @foreach($row->getAttributes() as $key => $value)

                                {{-- Sprawdź, czy pole jest edytowalne --}}
                                @if(!in_array($key, ['id', 'created_at', 'updated_at', 'email_verified_at', 'end_of_tracking_at', 'read_at']))

                                    {{-- Pole edytowalne --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold text-muted">{{ $key }}</label>
                                        <input type="text" name="{{ $key }}" class="form-control rounded-0 border-secondary" value="{{ old($key, $value) }}">
                                    </div>

                                @else

                                    {{-- Pole tylko do odczytu dla kolumn systemowych --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold text-muted">{{ $key }} (Zablokowane)</label>
                                        <input type="text" class="form-control bg-light text-muted rounded-0 border-secondary" value="{{ $value }}" disabled>
                                    </div>

                                @endif

                            @endforeach
                        </div>

                        <hr class="mt-4 mb-3 border-secondary">

                        {{-- Submit button --}}
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