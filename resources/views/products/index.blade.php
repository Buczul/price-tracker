@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <!-- Komunikat o sukcesie -->
                @if (session('success'))
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
                                <label class="form-label">Cena docelowa (Powiadom e-mail, gdy spadnie poniżej)</label>
                                <input type="number" step="0.01" name="target_price" class="form-control"
                                    placeholder="np. 150.00">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pierwszy link do produktu w sklepie</label>
                                <input type="url" name="url" class="form-control" required
                                    placeholder="https://...">
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
                                @php
                                    // Szukamy najnowszej ceny dla każdego linku przypisanego do produktu
                                    $currentPrices = $product->urls
                                        ->map(function ($url) {
                                            return $url->priceHistories->sortByDesc('created_at')->first()->price ??
                                                null;
                                        })
                                        ->filter(); // Usuwamy puste wartości (jeśli bot jeszcze nie pobrał ceny dla jakiegoś linku)

                                    // Wybieramy absolutnie najmniejszą wartość z zebranych najnowszych cen
                                    $lowestPrice = $currentPrices->min();
                                @endphp

                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h4 class="mb-1">{{ $product->name }}</h4>
                                        <div class="text-muted" style="font-size: 0.9rem;">
                                            <strong>Cena docelowa:</strong>
                                            {{ $product->target_price ? $product->target_price . ' PLN' : 'Nie ustawiono' }}
                                            <span class="mx-2">|</span>
                                            <strong>Aktualnie najtaniej:</strong>
                                            @if ($lowestPrice)
                                                <span
                                                    class="{{ $product->target_price && $lowestPrice <= $product->target_price ? 'text-success fw-bold' : 'text-primary fw-bold' }}">
                                                    {{ $lowestPrice }} PLN
                                                </span>
                                            @else
                                                Brak danych
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal" data-bs-target="#editProductModal-{{ $product->id }}">
                                            Edytuj produkt
                                        </button>
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                            onsubmit="return confirm('Czy na pewno chcesz usunąć TEN PRODUKT i wszystkie jego wykresy?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Usuń produkt</button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Modal Edycji Produktu (ukryty formularz) -->
                                <div class="modal fade" id="editProductModal-{{ $product->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('products.update', $product->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edytuj produkt</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Zamknij"></button>
                                                </div>

                                                <!-- Cała zawartość formularza musi być wewnątrz modal-body -->
                                                <div class="modal-body">

                                                    <!-- Pole nazwy produktu -->
                                                    <div class="mb-3">
                                                        <label class="form-label">Nazwa produktu</label>
                                                        <input type="text" name="name" class="form-control"
                                                            value="{{ $product->name }}" required>
                                                    </div>

                                                    <!-- Pole ceny docelowej -->
                                                    <div class="mb-3">
                                                        <label class="form-label">Cena docelowa (PLN)</label>
                                                        <input type="number" step="0.01" name="target_price"
                                                            class="form-control" value="{{ $product->target_price }}">
                                                    </div>

                                                </div>

                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formularz dodawania kolejnego linku -->
                                <form action="{{ route('products.urls.store', $product->id) }}" method="POST"
                                    class="d-flex gap-2 mb-4">
                                    @csrf
                                    <input type="url" name="url" class="form-control form-control-sm"
                                        placeholder="Wklej link do produktu z kolejnego skepu..." required>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Dodaj sklep</button>
                                </form>

                                @foreach ($product->urls as $url)
                                    <div class="mt-3">
                                        <div
                                            class="d-flex justify-content-between align-items-center mb-2 bg-light p-2 rounded">
                                            <div>
                                                <strong>Sklep:</strong> {{ $url->store_name }} |
                                                <a href="{{ $url->url }}" target="_blank"
                                                    class="text-decoration-none">Przejdź do sklepu &rarr;</a>
                                            </div>

                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editUrlModal-{{ $url->id }}">
                                                    Edytuj link
                                                </button>

                                                <form action="{{ route('urls.destroy', $url->id) }}" method="POST"
                                                    onsubmit="return confirm('Usunąć ten sklep i jego historię cen?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger">Usuń</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Modal Edycji Sklepu/Linku -->
                                        <div class="modal fade" id="editUrlModal-{{ $url->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('urls.update', $url->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edytuj dane sklepu</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Zamknij"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Nazwa sklepu</label>
                                                                <input type="text" name="store_name"
                                                                    class="form-control" value="{{ $url->store_name }}"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Link</label>
                                                                <input type="url" name="url" class="form-control"
                                                                    value="{{ $url->url }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Zapisz
                                                                zmiany</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Miejsce na wykres -->
                                        <div style="height: 300px; width: 100%;">
                                            <canvas id="chart-{{ $url->id }}"></canvas>
                                        </div>

                                        <!-- Skrypt rysujący wykres dla tego konkretnego linku -->
                                        <script>
                                            document.addEventListener("DOMContentLoaded", function() {
                                                const ctx = document.getElementById('chart-{{ $url->id }}').getContext('2d');

                                                const labels = {!! json_encode(
                                                    $url->priceHistories->take(-30)->pluck('created_at')->map->format('d.m.Y'),
                                                ) !!};
                                                const data = {!! json_encode($url->priceHistories->take(-30)->pluck('price')) !!};

                                                // 1. Pobieramy cenę docelową z poziomu Produktu
                                                const targetPrice = {!! json_encode($product->target_price) !!};

                                                // 2. Tworzymy podstawową tablicę serii danych (obecna cena)
                                                const datasets = [{
                                                    label: 'Cena w PLN',
                                                    data: data,
                                                    borderColor: 'rgb(75, 192, 192)',
                                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                                    tension: 0.3,
                                                    fill: true
                                                }];

                                                // 3. Jeśli cena docelowa jest ustawiona, dorzucamy poziomą linię
                                                if (targetPrice) {
                                                    datasets.push({
                                                        label: 'Cena docelowa',
                                                        // Logika JS: tworzy tablicę o długości etykiet i wypełnia ją stałą ceną docelową
                                                        data: Array(labels.length).fill(targetPrice),
                                                        borderColor: 'rgb(255, 99, 132)', // Estetyczny czerwony/różowy kolor
                                                        borderWidth: 2,
                                                        borderDash: [5, 5], // Robi linię przerywaną (kreski o dł. 5px, przerwy 5px)
                                                        pointRadius: 0, // Ukrywa kropki na tej linii
                                                        hoverRadius: 0, // Wyłącza podświetlanie po najechaniu
                                                        fill: false // Nie wypełniamy kolorem przestrzeni pod linią
                                                    });
                                                }

                                                new Chart(ctx, {
                                                    type: 'line',
                                                    data: {
                                                        labels: labels,
                                                        datasets: datasets // 4. Przekazujemy naszą przygotowaną dynamicznie tablicę
                                                    },
                                                    options: {
                                                        responsive: true,
                                                        maintainAspectRatio: false,
                                                        elements: {
                                                            point: {
                                                                // Jeśli jest tylko 1 pomiar, pokaż kropkę (5). Jeśli więcej, ukryj (0).
                                                                radius: data.length === 1 ? 5 : 0,
                                                                hitRadius: 10,
                                                                hoverRadius: 6
                                                            }
                                                        },
                                                        scales: {
                                                            x: {
                                                                ticks: {
                                                                    maxTicksLimit: 7,
                                                                    maxRotation: 0,
                                                                }
                                                            },
                                                            y: {
                                                                beginAtZero: false
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection
