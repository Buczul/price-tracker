@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h2 class="mb-0">📊 Panel Administratora</h2>
                <div class="nav nav-pills">
                    <a class="nav-link text-secondary" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="nav-link active" href="{{ route('admin.users.index') }}">Użytkownicy</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-bold">Zarządzanie Użytkownikami</div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 vertical-align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nazwa</th>
                                    <th>Adres E-mail</th>
                                    <th class="text-center">Śledzone produkty</th>
                                    <th class="text-center">Dodane sklepy</th>
                                    <th>Rola</th>
                                    <th class="text-end">Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td>{{ $user->email }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill">{{ $user->products_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark rounded-pill">{{ $user->urls_count }}</span>
                                        </td>
                                        <td>
                                            @if($user->is_admin)
                                                <span class="badge bg-danger">Administrator</span>
                                            @else
                                                <span class="badge bg-light text-dark border">Użytkownik</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-xs btn-outline-secondary disabled" style="font-size: 0.8rem;">Zarządzaj</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection