@extends('layouts.app')

@section('content')

{{-- Główne style dla tego widoku --}}
<style>
    html {
        scroll-behavior: smooth;
        overflow-y: scroll;
    }

    /* Magia ukrywania tekstu w przycisku rozwiń/zwiń */
    .toggle-btn[aria-expanded="true"] .text-expand { display: none; }
    .toggle-btn[aria-expanded="false"] .text-collapse { display: none; }
</style>

<div class="container-xl">
    <div class="row">

        {{-- LEWA KOLUMNA: Formularz dodawania nowego produktu --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 rounded-0 position-sticky shadow" style="top: 20px;">
                <div class="card-header border-0 rounded-0 fw-bold bg-primary text-white">Dodaj nowy produkt</div>
                <div class="card-body">
                    <form action="{{ route('products.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nazwa produktu</label>
                            <input type="text" name="name" class="form-control form-control-sm" required placeholder="np. Klawiatura Razer">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold d-flex align-items-center gap-1">
                                Cena docelowa (PLN) <span class="text-muted fw-normal">(opcjonalne)</span>
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="number" step="0.01" name="target_price" class="form-control form-control-sm" placeholder="np. 150.00">
                                <span class="fs-5 lh-1" style="cursor: help;" title="Jeżeli cena produktu w sklepie spadnie poniżej docelowej, poinformujemy Cię o tym mailem. Cenę docelową można dodać później.">
                                    ℹ️
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pierwszy link do sklepu</label>
                            <input type="url" name="url" class="form-control form-control-sm" required placeholder="https://...">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold text-white">Zapisz produkt</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ŚRODKOWA KOLUMNA: Lista śledzonych produktów i filtry --}}
        <div class="col-lg-7 mb-4">

            {{-- Wyświetlanie komunikatu o sukcesie --}}
            @if (session('success'))
                <div class="alert alert-success shadow">{{ session('success') }}</div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning shadow fw-bold">
                    ⏳ {{ session('warning') }}
                </div>
            @endif

            <div class="card border-0 rounded-0 shadow">
                <div class="card-header border-0 rounded-0 fw-bold bg-primary text-white">Twoje śledzone produkty</div>

                {{-- Sekcja z formularzem wyszukiwania, sortowania i filtrowania --}}
                <div class="card border-0 rounded-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <form action="{{ route('products.index') }}" method="GET" id="filter-form">
                            <div class="row g-2 align-items-center">

                                {{-- Pole wyszukiwania po nazwie --}}
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <input type="text" name="wyszukaj" class="form-control" placeholder="Szukaj produktu..." value="{{ request('wyszukaj') }}">
                                        <button class="btn btn-primary" type="submit" title="Szukaj">
                                            🔍
                                        </button>
                                    </div>
                                </div>

                                {{-- Opcje sortowania --}}
                                <div class="col-md-4">
                                    <select name="sortowanie" class="form-select text-muted" onchange="document.getElementById('filter-form').submit();">
                                        <option value="najnowsze" {{ request('sortowanie') == 'najnowsze' ? 'selected' : '' }}>Od najnowszego</option>
                                        <option value="najstarsze" {{ request('sortowanie') == 'najstarsze' ? 'selected' : '' }}>Od najstarszego</option>
                                        <option value="cel_rosnaco" {{ request('sortowanie') == 'cel_rosnaco' ? 'selected' : '' }}>Cena docelowa (rosnąco)</option>
                                        <option value="aktualna_rosnaco" {{ request('sortowanie') == 'aktualna_rosnaco' ? 'selected' : '' }}>Cena aktualna (najtańsze)</option>
                                    </select>
                                </div>

                                {{-- Przycisk rozwijający zaawansowane filtry --}}
                                <div class="col-md-3 text-end">
                                    <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#advanced-filters" aria-expanded="false">
                                        Filtry ⚙️
                                    </button>
                                </div>
                            </div>

                            {{-- Rozwijana sekcja zaawansowanych filtrów --}}
                            <div class="collapse {{ request()->anyFilled(['sklepy_od', 'sklepy_do', 'cel_od', 'cel_do', 'aktualna_od', 'aktualna_do']) ? 'show' : '' }} mt-3" id="advanced-filters">
                                <div class="p-3">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small fw-bold text-muted mb-1">Liczba podpiętych sklepów</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="sklepy_od" class="form-control" placeholder="Od" value="{{ request('sklepy_od') }}">
                                                <span class="input-group-text bg-white border-start-0 border-end-0 text-muted">-</span>
                                                <input type="number" name="sklepy_do" class="form-control" placeholder="Do" value="{{ request('sklepy_do') }}">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="small fw-bold text-muted mb-1">Cena docelowa (PLN)</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" name="cel_od" class="form-control" placeholder="Od" value="{{ request('cel_od') }}">
                                                <span class="input-group-text bg-white border-start-0 border-end-0 text-muted">-</span>
                                                <input type="number" step="0.01" name="cel_do" class="form-control" placeholder="Do" value="{{ request('cel_do') }}">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="small fw-bold text-muted mb-1">Cena aktualna (PLN)</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" name="aktualna_od" class="form-control" placeholder="Od" value="{{ request('aktualna_od') }}">
                                                <span class="input-group-text bg-white border-start-0 border-end-0 text-muted">-</span>
                                                <input type="number" step="0.01" name="aktualna_do" class="form-control" placeholder="Do" value="{{ request('aktualna_do') }}">
                                            </div>
                                        </div>

                                        <div class="col-12 d-flex justify-content-end align-items-center gap-3 mt-3 pt-2 border-top">
                                            <a href="{{ route('products.index') }}" class="text-decoration-none small text-danger fw-bold">✖ Wyczyść filtry</a>
                                            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4 text-white">Filtruj wyniki</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Główna pętla wyświetlająca produkty --}}
                <div class="card-body">
                    @forelse($products as $product)
                        <div id="product-{{ $product->id }}" class="mb-5 border-bottom pb-4" style="scroll-margin-top: 20px;">

                            {{-- Obliczenia pomocnicze dla najniższej ceny produktu --}}
                            @php
                                $currentPrices = $product->urls
                                    ->map(function ($urlModel) {
                                        return $urlModel->priceHistories->sortByDesc('created_at')->first()->price ?? null;
                                    })->filter();

                                $lowestPrice = $currentPrices->min();
                            @endphp

                            {{-- Nagłówek pojedynczego produktu (Tytuł i ceny) --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="mb-1 d-flex align-items-center gap-2">
                                        @if($product->is_favourite)
                                            <span class="text-warning fs-5">⭐</span>
                                        @endif
                                        {{ $product->name }}
                                    </h4>
                                    <div class="text-muted" style="font-size: 0.9rem;">
                                        <strong>Cena docelowa:</strong>
                                        @if ($product->target_price)
                                            {{ $product->target_price }} PLN
                                        @else
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none ms-1 fw-bold align-baseline" data-bs-toggle="modal" data-bs-target="#edit-product-modal-{{ $product->id }}">
                                                Dodaj cenę docelową
                                            </button>
                                        @endif

                                        <span class="mx-2">|</span>

                                        <strong>Aktualnie najtaniej:</strong>
                                        @if ($lowestPrice)
                                            <span class="{{ $product->target_price && $lowestPrice <= $product->target_price ? 'text-success fw-bold' : 'text-primary fw-bold' }}">
                                                {{ $lowestPrice }} PLN
                                            </span>
                                        @else
                                            Brak danych
                                        @endif
                                    </div>
                                </div>

                                {{-- Przycisk rozwijający szczegóły produktu (wykres, linki) --}}
                                <div class="mt-1">
                                    <button class="btn btn-sm btn-outline-primary toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $product->id }}" aria-expanded="false" aria-controls="collapse-{{ $product->id }}">
                                        <span class="text-expand">Rozwiń 🔽</span>
                                        <span class="text-collapse">Zwiń 🔼</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Rozwijany kontener z detalami produktu --}}
                            <div class="collapse" id="collapse-{{ $product->id }}">

                                {{-- Przyciski akcji dla produktu (Ulubione, Edycja, Usuwanie) --}}
                                <div class="d-flex justify-content-end align-items-center gap-2 pt-3 mb-4">
                                    <form action="{{ route('products.favourite', $product->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $product->is_favourite ? 'btn-warning border-warning text-dark' : 'btn-outline-warning text-dark' }}" title="Ulubione">
                                            ⭐
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#edit-product-modal-{{ $product->id }}">
                                        Edytuj produkt ⚙️
                                    </button>

                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="m-0" onsubmit="return confirm('Czy na pewno chcesz przestać śledzić TEN PRODUKT?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Przestań śledzić ✖️</button>
                                    </form>
                                </div>

                                {{-- Formularz szybkiego dodawania nowego sklepu (linku) --}}
                                <form action="{{ route('products.urls.store', $product->id) }}" method="POST" class="d-flex gap-2 mb-4 mt-3">
                                    @csrf
                                    <input type="url" name="url" class="form-control form-control-sm bg-light" placeholder="Wklej link z kolejnego sklepu..." required>
                                    <button type="submit" class="btn btn-sm btn-outline-primary whitespace-nowrap text-nowrap">Dodaj sklep</button>
                                </form>

                                {{-- Pętla po podpiętych sklepach (linkach) dla danego produktu --}}
                                @foreach ($product->urls as $urlModel)
                                    <div class="mt-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1 bg-light p-2 rounded border border-light">
                                            <div class="small">
                                                <strong>{{ $urlModel->store_name }}</strong> |
                                                <a href="{{ $urlModel->url }}" target="_blank" class="text-decoration-none">Sklep &rarr;</a>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#edit-url-modal-{{ $urlModel->id }}">
                                                    Edytuj sklep ⚙️
                                                </button>

                                                <form action="{{ route('urls.destroy', $urlModel->id) }}" method="POST" class="m-0" onsubmit="return confirm('Przestać śledzić ten sklep?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Przestań śledzić ✖️</button>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- Modal edycji dla konkretnego sklepu (linku) --}}
                                        <div class="modal fade" id="edit-url-modal-{{ $urlModel->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('urls.update', $urlModel->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edytuj dane sklepu</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Adres www sklepu (host)</label>
                                                                <input type="text" name="store_name" class="form-control" value="{{ $urlModel->store_name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Link do produktu</label>
                                                                <input type="url" name="url" class="form-control" value="{{ $urlModel->url }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Logika pobierania danych do wykresu z Chart.js --}}
                                @php
                                    // Pobieranie danych społeczności z bazy
                                    $communityHistory = collect();
                                    foreach($product->urls as $urlModel) {
                                        $oldPrices = \App\Models\PriceHistory::whereHas('url', function($query) use ($urlModel) {
                                                $query->where('url', $urlModel->url);
                                            })
                                            ->where('created_at', '<', $urlModel->created_at)
                                            ->get();
                                        $communityHistory = $communityHistory->merge($oldPrices);
                                    }

                                    // Łączenie wszystkich dat
                                    $allDatesCarbon = $product->urls->flatMap(function($urlModel) {
                                        return $urlModel->priceHistories->pluck('created_at');
                                    })->merge($communityHistory->pluck('created_at'))
                                    ->unique(function($dateEntry) {
                                        return $dateEntry->format('Y-m-d');
                                    })
                                    ->sortBy(function($dateEntry) {
                                        return $dateEntry->timestamp;
                                    })
                                    ->values();

                                    // Przyjazny format dla etykiet osi X na wykresie
                                    $allDatesFormatted = $allDatesCarbon->map->format('d.m.Y');

                                    // Techniczny format do porównań kalendarzowych
                                    $allDatesISO = $allDatesCarbon->map->format('Y-m-d');
                                @endphp

                                @if($product->urls->count() > 0)
                                    {{-- Panel kontrolny wykresu (widoczność linii i kalendarze) --}}
                                    <div class="mt-4 mb-3 p-3 bg-light rounded border border-light d-flex flex-column flex-md-row justify-content-between gap-3">
                                        <div>
                                            <div class="fw-bold small text-muted mb-2 px-1">Wyświetlane na wykresie:</div>
                                            <div class="d-flex flex-wrap gap-2 px-1">
                                                @foreach($product->urls as $index => $urlModel)
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input store-toggle-{{ $product->id }}" type="checkbox" role="switch"
                                                            id="toggle-{{ $urlModel->id }}" data-index="{{ $index }}" checked>
                                                        <label class="form-check-label small" for="toggle-{{ $urlModel->id }}">
                                                            {{ $urlModel->store_name }}
                                                        </label>
                                                    </div>
                                                @endforeach

                                                @if($communityHistory->isNotEmpty())
                                                    <div class="form-check form-switch border-start ps-4 ms-2">
                                                        <input class="form-check-input store-toggle-{{ $product->id }}" type="checkbox" role="switch"
                                                            id="toggle-community-{{ $product->id }}" data-index="{{ $product->urls->count() }}" checked>
                                                        <label class="form-check-label small text-muted fw-bold" for="toggle-community-{{ $product->id }}">
                                                            Dane archiwalne od innych użytkowników
                                                        </label>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            <div class="fw-bold small text-muted mb-2 px-1">Zakres dat:</div>
                                            <div class="d-flex align-items-center gap-2 px-1">
                                                <input type="date" id="date-start-{{ $product->id }}" class="form-control form-control-sm text-muted" style="width: 130px;">
                                                <span class="text-muted small">-</span>
                                                <input type="date" id="date-end-{{ $product->id }}" class="form-control form-control-sm text-muted" style="width: 130px;">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Miejsce docelowe (canvas) na renderowanie wykresu --}}
                                    <div class="mb-4 bg-white p-2 border-0 rounded shadow-sm" style="height: 320px; width: 100%;">
                                        <canvas id="product-chart-{{ $product->id }}"></canvas>
                                    </div>

                                    {{-- Komunikat i przycisk do pobrania cen, jeśli produkt nie ma jeszcze historii --}}
                                    @if($currentPrices->isEmpty())
                                        <div class="mb-4 text-center bg-light p-3 rounded border border-light">
                                            <form action="{{ route('products.check-prices', $product->id) }}" method="POST" class="d-inline price-fetch-form">
                                                @csrf
                                                <button type="submit" class="btn btn-success fw-bold px-4">
                                                    Sprawdź ceny po raz pierwszy
                                                </button>
                                            </form>
                                            <span class="ms-2 fs-5 align-middle text-muted" style="cursor: help;" title="Możesz sprawdzić ceny ze sklepów dla twojego produktu po raz pierwszy. Ceny sprawdzane i dodawane do bazy są automatycznie o godzinie 02:00.">
                                                ℹ️
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Skrypt rysujący wykres dla tego konkretnego produktu --}}
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            const chartContext = document.getElementById('product-chart-{{ $product->id }}').getContext('2d');

                                            // Zmienne zasilające wykres danymi z PHP
                                            const allLabels = {!! json_encode($allDatesFormatted) !!};
                                            const allDatesISO = {!! json_encode($allDatesISO) !!};
                                            const targetPrice = {!! json_encode($product->target_price) !!};
                                            const originalDatasets = [];

                                            const chartColors = [
                                                'rgb(75, 192, 192)', 'rgb(54, 162, 235)', 'rgb(255, 159, 64)',
                                                'rgb(153, 102, 255)', 'rgb(255, 205, 86)', 'rgb(201, 203, 207)'
                                            ];

                                            @foreach($product->urls as $index => $urlModel)
                                                @php
                                                    $mappedPrices = $allDatesFormatted->map(function($labelDate) use ($urlModel) {
                                                        $entry = $urlModel->priceHistories->first(function($history) use ($labelDate) {
                                                            return $history->created_at->format('d.m.Y') === $labelDate;
                                                        });
                                                        return $entry ? $entry->price : null;
                                                    });
                                                @endphp

                                                originalDatasets.push({
                                                    label: '{{ $urlModel->store_name }}',
                                                    data: {!! json_encode($mappedPrices->values()) !!},
                                                    borderColor: chartColors[{{ $index }} % chartColors.length],
                                                    backgroundColor: chartColors[{{ $index }} % chartColors.length].replace('rgb', 'rgba').replace(')', ', 0.1)'),
                                                    tension: 0,
                                                    fill: false
                                                });
                                            @endforeach

                                            @if($communityHistory->isNotEmpty())
                                                @php
                                                    $communityPricesList = [];
                                                    $communityStoresList = [];

                                                    foreach ($allDatesFormatted as $labelDate) {
                                                        $dailyEntries = $communityHistory->filter(function($history) use ($labelDate) {
                                                            return $history->created_at->format('d.m.Y') === $labelDate;
                                                        });

                                                        if ($dailyEntries->isNotEmpty()) {
                                                            $cheapestDailyEntry = $dailyEntries->sortBy('price')->first();
                                                            $communityPricesList[] = $cheapestDailyEntry->price;
                                                            $communityStoresList[] = $cheapestDailyEntry->url->store_name ?? 'Nieznany sklep';
                                                        } else {
                                                            $communityPricesList[] = null;
                                                            $communityStoresList[] = null;
                                                        }
                                                    }
                                                @endphp

                                                originalDatasets.push({
                                                    label: 'Dane innych użytkowników',
                                                    data: {!! json_encode($communityPricesList) !!},
                                                    sklepy: {!! json_encode($communityStoresList) !!},
                                                    borderColor: 'rgba(108, 117, 125, 0.6)',
                                                    borderDash: [5, 5],
                                                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                                                    tension: 0,
                                                    fill: false
                                                });
                                            @endif

                                            if (targetPrice) {
                                                originalDatasets.push({
                                                    label: 'Cena docelowa',
                                                    data: Array(allLabels.length).fill(targetPrice),
                                                    borderColor: 'rgb(255, 99, 132)',
                                                    borderWidth: 2,
                                                    borderDash: [5, 5],
                                                    pointRadius: 0,
                                                    hoverRadius: 0,
                                                    fill: false
                                                });
                                            }

                                            // Inicjalizacja instancji wykresu
                                            const chartInstance = new Chart(chartContext, {
                                                type: 'line',
                                                data: { labels: [], datasets: originalDatasets.map(dataset => Object.assign({}, dataset)) },
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    elements: {
                                                        point: {
                                                            radius: function(context) {
                                                                const validEntries = context.dataset.data.filter(price => price !== null).length;
                                                                return validEntries === 1 ? 4 : 0;
                                                            },
                                                            hitRadius: 10,
                                                            hoverRadius: 6
                                                        }
                                                    },
                                                    plugins: {
                                                        tooltip: {
                                                            callbacks: {
                                                                label: function(tooltipContext) {
                                                                    let labelText = tooltipContext.dataset.label || '';
                                                                    if (labelText) labelText += ': ';

                                                                    if (tooltipContext.parsed.y !== null) {
                                                                        labelText += tooltipContext.parsed.y + ' PLN';
                                                                        if (tooltipContext.dataset.sklepy && tooltipContext.dataset.sklepy[tooltipContext.dataIndex]) {
                                                                            labelText += ' (' + tooltipContext.dataset.sklepy[tooltipContext.dataIndex] + ')';
                                                                        }
                                                                    }
                                                                    return labelText;
                                                                }
                                                            }
                                                        }
                                                    },
                                                    scales: {
                                                        x: { ticks: { maxTicksLimit: 7, maxRotation: 0 } },
                                                        y: { beginAtZero: false }
                                                    }
                                                }
                                            });

                                            const dateStartInput = document.getElementById('date-start-{{ $product->id }}');
                                            const dateEndInput = document.getElementById('date-end-{{ $product->id }}');

                                            // Ustawienie domyślnych dat - start wyświetlania na "ostatnie 30 wpisów"
                                            if (allDatesISO.length > 0) {
                                                const defaultIndex = Math.max(0, allDatesISO.length - 30);
                                                dateStartInput.value = allDatesISO[defaultIndex];
                                                dateEndInput.value = allDatesISO[allDatesISO.length - 1];
                                            }

                                            // Funkcja filtrująca wykres na podstawie wybranych dat w kalendarzu
                                            function filterChart() {
                                                const start = dateStartInput.value;
                                                const end = dateEndInput.value;

                                                const filteredIndices = [];
                                                allDatesISO.forEach((iso, index) => {
                                                    if ((!start || iso >= start) && (!end || iso <= end)) {
                                                        filteredIndices.push(index);
                                                    }
                                                });

                                                chartInstance.data.labels = filteredIndices.map(i => allLabels[i]);

                                                chartInstance.data.datasets.forEach((dataset, j) => {
                                                    if (originalDatasets[j].label === 'Cena docelowa') {
                                                        dataset.data = Array(filteredIndices.length).fill(targetPrice);
                                                    } else {
                                                        dataset.data = filteredIndices.map(i => originalDatasets[j].data[i]);
                                                        if (originalDatasets[j].sklepy) {
                                                            dataset.sklepy = filteredIndices.map(i => originalDatasets[j].sklepy[i]);
                                                        }
                                                    }
                                                });

                                                chartInstance.update();
                                            }

                                            // Aktualizacja w czasie rzeczywistym
                                            dateStartInput.addEventListener('change', filterChart);
                                            dateEndInput.addEventListener('change', filterChart);

                                            // Uruchomienie filtra przy ładowaniu
                                            filterChart();

                                            // Obsługa przełączników sklepów (checkboxów pod wykresem)
                                            document.querySelectorAll('.store-toggle-{{ $product->id }}').forEach(toggle => {
                                                toggle.addEventListener('change', function() {
                                                    const datasetIndex = this.dataset.index;
                                                    chartInstance.setDatasetVisibility(datasetIndex, this.checked);
                                                    chartInstance.update();
                                                });
                                            });
                                        });
                                    </script>
                                @endif

                            </div>

                            {{-- Modal służący do edycji podstawowych danych produktu --}}
                            <div class="modal fade" id="edit-product-modal-{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('products.update', $product->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edytuj produkt</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Nazwa produktu</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Cena docelowa (PLN)</label>
                                                    <input type="number" step="0.01" name="target_price" class="form-control" value="{{ $product->target_price }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @empty
                        {{-- Komunikat wyświetlany, gdy użytkownik nie ma żadnych produktów --}}
                        <div class="text-center p-5 text-muted">
                            <h5>Nie śledzisz jeszcze żadnych produktów</h5>
                            <p>Skorzystaj z formularza po lewej stronie, aby zacząć.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- PRAWA KOLUMNA: Lista ulubionych produktów --}}
        <div class="col-lg-2">
            <div class="card position-sticky rounded-0 shadow border-0" style="top: 20px;">
                <div class="card-header fw-bold bg-warning bg-opacity-25 text-dark rounded-0 border-0 d-flex align-items-center gap-2">
                    ⭐ Ulubione
                </div>
                <div class="card-body p-0">
                    @php
                        $favouriteProducts = $products->where('is_favourite', true);
                    @endphp

                    @if($favouriteProducts->count() > 0)
                        <div class="list-group list-group-flush rounded-bottom">
                            @foreach($favouriteProducts as $favouriteModel)
                                @php
                                    $favouriteCurrentPrices = $favouriteModel->urls->map(function ($urlModel) {
                                        return $urlModel->priceHistories->sortByDesc('created_at')->first()->price ?? null;
                                    })->filter();

                                    $favouriteLowestPrice = $favouriteCurrentPrices->min();
                                @endphp

                                <a href="#product-{{ $favouriteModel->id }}" class="list-group-item list-group-item-action py-2">
                                    <div class="fw-semibold text-primary mb-1" style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $favouriteModel->name }}">
                                        {{ $favouriteModel->name }}
                                    </div>

                                    <div class="d-flex flex-column text-muted" style="font-size: 0.75rem;">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Cel:</span>
                                            <span>{{ $favouriteModel->target_price ? $favouriteModel->target_price . ' PLN' : '-' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Aktualnie:</span>
                                            @if ($favouriteLowestPrice)
                                                <span class="{{ $favouriteModel->target_price && $favouriteLowestPrice <= $favouriteModel->target_price ? 'text-success fw-bold' : 'text-dark fw-bold' }}">
                                                    {{ $favouriteLowestPrice }} PLN
                                                </span>
                                            @else
                                                <span>Brak</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        {{-- Komunikat wyświetlany przy braku ulubionych --}}
                        <div class="p-4 text-muted small text-center" style="font-size: 0.8rem;">
                            Brak ulubionych. Zaznacz gwiazdkę przy ważnych dla Ciebie produktach!
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Skrypt odpowiedzialny za renderowanie wykresów Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Globalne skrypty UI: zapamiętywanie scrolla i stanu akordeonów --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Obsługa zapisywania stanu rozwiniętych/zwiniętych paneli produktów
        let openAccordions = JSON.parse(sessionStorage.getItem('openAccordions') || '[]');

        openAccordions.forEach(function(id) {
            let element = document.getElementById(id);
            if (element) {
                element.classList.add('show');
                let toggleBtn = document.querySelector(`[data-bs-target="#${id}"]`);
                if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
            }
        });

        document.querySelectorAll('.collapse').forEach(function(collapsibleElement) {
            collapsibleElement.addEventListener('shown.bs.collapse', function(event) {
                let openList = JSON.parse(sessionStorage.getItem('openAccordions') || '[]');
                if (!openList.includes(event.target.id)) {
                    openList.push(event.target.id);
                    sessionStorage.setItem('openAccordions', JSON.stringify(openList));
                }
            });

            collapsibleElement.addEventListener('hidden.bs.collapse', function(event) {
                let openList = JSON.parse(sessionStorage.getItem('openAccordions') || '[]');
                openList = openList.filter(id => id !== event.target.id);
                sessionStorage.setItem('openAccordions', JSON.stringify(openList));
            });
        });

        // Przywracanie pozycji scrolla po przeładowaniu strony
        let scrollPos = sessionStorage.getItem('scrollpos');
        if (scrollPos) {
            setTimeout(function() {
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem('scrollpos');
            }, 50);
        }
    });

    window.addEventListener("beforeunload", function () {
        sessionStorage.setItem('scrollpos', window.scrollY);
    });
</script>

{{-- Ekran ładowania (loader) wyświetlany podczas ręcznego pobierania cen z API --}}
<div id="loading-screen" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; color: white;">
    <div class="spinner-border text-light mb-4" role="status" style="width: 4rem; height: 4rem;"></div>
    <h4 class="fw-bold">Trwa komunikacja ze sklepami...</h4>
    <p class="text-light opacity-75 mt-2">To może potrwać kilkanaście sekund. Proszę nie zamykać okna.</p>
</div>

{{-- Skrypt aktywujący ekran ładowania --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.price-fetch-form').forEach(form => {
            form.addEventListener('submit', function() {
                document.getElementById('loading-screen').style.display = 'flex';
            });
        });
    });
</script>
@endsection