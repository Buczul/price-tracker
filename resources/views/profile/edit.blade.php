@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            @if(session('success'))
                <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card border-0 rounded-0 mb-4 shadow">
                    <div class="card-header border-0 rounded-0 fw-bold bg-white">Ustawienia</div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="notifySwitch"
                                   name="notify_via_email" value="1"
                                   {{ $user->notify_via_email ? 'checked' : '' }}> <label class="form-check-label fw-bold" for="notifySwitch">
                                Chcę otrzymywać powiadomienia e-mail o spadku cen
                            </label>
                            <p class="text-muted small mb-0">
                                Jeśli wyłączysz tę opcję, będziesz widzieć powiadomienia tylko w aplikacji.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card border-0 rounded-0 shadow">
                    <div class="card-header border-0 rounded-0 fw-bold bg-white">Zmień dane profilu</div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nazwa użytkownika</label>
                            <input type="text" name="nazwa" class="form-control @error('nazwa') is-invalid @enderror" value="{{ old('nazwa', $user->name) }}" required>
                            @error('nazwa') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adres E-mail</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <hr class="my-4">

                        <div class="mb-4">
                            <label class="form-label fw-bold text-danger">Aktualne hasło (wymagane do zapisu wszystkich zmian)</label>
                            <input type="password" name="aktualne_haslo" class="form-control @error('aktualne_haslo') is-invalid @enderror" required placeholder="Wpisz swoje obecne hasło...">
                            @error('aktualne_haslo') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <h6 class="text-muted mb-3 mt-4">Zmiana hasła (zostaw puste, jeśli nie chcesz zmieniać)</h6>

                        <div class="mb-3">
                            <label class="form-label">Nowe hasło</label>
                            <input type="password" name="nowe_haslo" class="form-control @error('nowe_haslo') is-invalid @enderror">
                            @error('nowe_haslo') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Potwierdź nowe hasło</label>
                            <input type="password" name="nowe_haslo_confirmation" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold px-4 text-white">Zapisz zmiany</button>
                    </div>
                </div>
            </form>

            <div class="card border-0 rounded-0 mt-4 shadow">
                <div class="card-header border-0 rounded-0 bg-danger text-white fw-bold">Usuwanie konta</div>
                <div class="card-body">
                    <p class="text-muted small">
                        Usunięcie konta jest operacją permanentną i nieodwracalną. Wszystkie Twoje śledzone produkty, skonfigurowane linki oraz cała zebrana historia cen zostaną bezpowrotnie skasowane z bazy danych.
                    </p>

                    <form action="{{ route('profile.destroy') }}" method="POST" onsubmit="return confirm('Czy na pewno chcesz bezpowrotnie usunąć swoje konto i wszystkie dane?');">
                        @csrf
                        @method('DELETE')

                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Potwierdź hasłem usunięcie konta:</label>
                            <input type="password" name="haslo_do_usuniecia" class="form-control @error('haslo_do_usuniecia') is-invalid @enderror" placeholder="Wpisz swoje aktualne hasło" required>
                            @error('haslo_do_usuniecia')
                                <span class="text-danger small d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-danger">Usuń moje konto</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection