@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <!-- Komunikat o sukcesie -->
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Formularz dodawania -->
            <div class="card mb-4">
                <div class="card-header">Dodaj nowy produkt do śledzenia</div>
                <div class="card-body">
                    <form action="{{ route('products.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nazwa produktu (np. Klawiatura Razer)</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link do sklepu</label>
                            <input type="url" name="url" class="form-control" required placeholder="https://...">
                        </div>
                        <button type="submit" class="btn btn-primary">Zapisz produkt</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Twoje śledzone produkty</div>
                <div class="card-body">
                    @forelse($products as $product)
                        <div class="mb-5 border-bottom pb-4">
                            <h4>{{ $product->name }}</h4>

                            @foreach($product->urls as $url)
                                <div class="mt-3">
                                    <p class="mb-1">
                                        <strong>Sklep:</strong> {{ $url->store_name }} |
                                        <a href="{{ $url->url }}" target="_blank" class="text-decoration-none">Przejdź do sklepu &rarr;</a>
                                    </p>

                                    <!-- Miejsce na wykres -->
                                    <div style="height: 300px; width: 100%;">
                                        <canvas id="chart-{{ $url->id }}"></canvas>
                                    </div>

                                    <!-- Skrypt rysujący wykres dla tego konkretnego linku -->
                                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            const ctx = document.getElementById('chart-{{ $url->id }}').getContext('2d');

                                            // Pobieramy dane z PHP (Laravela) do JavaScriptu
                                            const labels = {!! json_encode($url->priceHistories->pluck('created_at')->map->format('Y-m-d H:i')) !!};
                                            const data = {!! json_encode($url->priceHistories->pluck('price')) !!};

                                            new Chart(ctx, {
                                                type: 'line',
                                                data: {
                                                    labels: labels,
                                                    datasets: [{
                                                        label: 'Cena w PLN',
                                                        data: data,
                                                        borderColor: 'rgb(75, 192, 192)',
                                                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                                        tension: 0.3, // Lekko zaokrąglone linie
                                                        fill: true
                                                    }]
                                                },
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    scales: {
                                                        y: {
                                                            beginAtZero: false // Wykres nie musi zaczynać się od zera, lepiej widać wahania
                                                        }
                                                    }
                                                }
                                            });
                                        });
                                    </script>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p class="mb-0">Nie śledzisz jeszcze żadnych produktów.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection