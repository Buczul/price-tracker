@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    {{-- Nagłówek i nawigacja --}}
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h2 class="mb-0">⚙️ Zarządzanie Bazą Danych</h2>
        <div class="nav nav-pills">
            <a class="nav-link text-secondary me-2 rounded-0" href="{{ route('admin.dashboard') }}">Wróć do Dashboardu</a>
            <a class="nav-link active rounded-0" href="#">Zarządzanie Bazą Danych</a>
        </div>
    </div>

    <div class="row">

        {{-- Pasek boczny: Wybór tabeli --}}
        <div class="col-md-2 mb-4">
            <div class="list-group shadow-sm rounded-0 border-0">
                <div class="list-group-item bg-dark text-white fw-bold text-center border-0 rounded-0">Tabele Systemowe</div>
                <a href="{{ route('admin.index', 'uzytkownicy') }}" class="list-group-item list-group-item-action border-0 rounded-0 {{ $table == 'uzytkownicy' ? 'active fw-bold' : '' }}">👥 Użytkownicy</a>
                <a href="{{ route('admin.index', 'produkty') }}" class="list-group-item list-group-item-action border-0 rounded-0 {{ $table == 'produkty' ? 'active fw-bold' : '' }}">📦 Produkty</a>
                <a href="{{ route('admin.index', 'linki_sklepow') }}" class="list-group-item list-group-item-action border-0 rounded-0 {{ $table == 'linki_sklepow' ? 'active fw-bold' : '' }}">🔗 Linki Sklepów</a>
                <a href="{{ route('admin.index', 'historie_cen') }}" class="list-group-item list-group-item-action border-0 rounded-0 {{ $table == 'historie_cen' ? 'active fw-bold' : '' }}">📉 Historie Cen</a>
                <a href="{{ route('admin.index', 'powiadomienia') }}" class="list-group-item list-group-item-action border-0 rounded-0 {{ $table == 'powiadomienia' ? 'active fw-bold' : '' }}">🔔 Powiadomienia</a>
            </div>
        </div>

        {{-- Treść główna: Tabela danych --}}
        <div class="col-md-10">

            {{-- Powiadomienie o sukcesie --}}
            @if(session('success'))
                <div class="alert alert-success shadow-sm border-0 rounded-0">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm border-0 rounded-0">

                {{-- Nagłówek karty z informacjami o tabeli --}}
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3 border-0 rounded-0 border-bottom">
                    <span class="fs-5">Tabela: <span class="text-primary">{{ strtoupper($table) }}</span></span>
                    <span class="badge bg-secondary rounded-0">Rekordów na stronie: {{ $rows->count() }}</span>
                </div>

                <div class="card-body p-0 table-responsive">
                    @if($rows->count() > 0)
                        <table class="table table-hover table-striped table-sm align-middle mb-0" style="font-size: 0.85rem;">

                            {{-- Dynamiczne nagłówki tabel --}}
                            <thead class="table-dark">
                                <tr>
                                    @foreach(array_keys($rows->first()->getAttributes()) as $column)
                                        <th class="py-2">{{ $column }}</th>
                                    @endforeach
                                    <th class="text-end pe-3">Akcje</th>
                                </tr>
                            </thead>

                            {{-- Dynamiczne wiersze tabeli --}}
                            <tbody>
                                @foreach($rows as $row)
                                    <tr>
                                        @foreach($row->getAttributes() as $key => $value)
                                            <td class="text-truncate" style="max-width: 150px;" title="{{ $value }}">
                                                {{ $value === null ? 'NULL' : $value }}
                                            </td>
                                        @endforeach

                                        {{-- Przyciski akcji --}}
                                        <td class="text-end text-nowrap pe-2">
                                            <a href="{{ route('admin.edit', ['table' => $table, 'id' => $row->id]) }}" class="btn btn-sm btn-outline-primary py-0 px-2 me-1 rounded-0 border-secondary">Edytuj</a>

                                            <form action="{{ route('admin.destroy', ['table' => $table, 'id' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('UWAGA: Całkowite usunięcie rekordu. Tej akcji nie można cofnąć! Kontynuować?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger py-0 px-2 rounded-0">Usuń</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    @else
                        {{-- Komunikat o pustym stanie --}}
                        <div class="p-5 text-center text-muted fs-5">Brak danych w tej tabeli.</div>
                    @endif
                </div>
            </div>

            {{-- Wiersze listy linków --}}
            <div class="mt-4">
                {{ $rows->links() }}
            </div>

        </div>
    </div>
</div>
@endsection