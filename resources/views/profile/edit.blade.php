@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">Ustawienia profilu</div>

                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nazwa użytkownika</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adres E-mail</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <hr class="my-4">
                        <h6 class="text-muted mb-3">Zmiana hasła (zostaw puste, jeśli nie chcesz zmieniać)</h6>

                        <div class="mb-3">
                            <label class="form-label">Nowe hasło</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Potwierdź nowe hasło</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                    </form>
                </div>

            </div>

            <div class="card border-danger mt-4">
                <div class="card-header bg-danger text-white fw-bold">Usuwanie konta</div>
                <div class="card-body">
                    <p class="text-muted small">
                        Usunięcie konta jest operacją permanentną i nieodwracalną. Wszystkie Twoje śledzone produkty, skonfigurowane linki oraz cała zebrana historia cen zostaną bezpowrotnie skasowane z bazy danych.
                    </p>

                    <form action="{{ route('profile.destroy') }}" method="POST" onsubmit="return confirm('Czy na pewno chcesz bezpowrotnie usunąć swoje konto i wszystkie dane?');">
                        @csrf
                        @method('DELETE')

                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Potwierdź hasłem usunięcie konta:</label>
                            <input type="password" name="delete_password" class="form-control @error('delete_password') is-invalid @enderror" placeholder="Wpisz swoje aktualne hasło" required>
                            @error('delete_password')
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