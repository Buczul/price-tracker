@extends('layouts.app')

@section('content')

<style>
    html {
        scroll-behavior: smooth;
        overflow-y: scroll;
    }

    /* Magia zmieniającego się tekstu przycisku Rozwiń/Zwiń */
    .toggle-btn[aria-expanded="true"] .text-expand { display: none; }
    .toggle-btn[aria-expanded="false"] .text-collapse { display: none; }
</style>

    <div class="container-xl">
        <div class="row">

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
                                <input type="url" name="url" class="form-control form-control-sm" required
                                    placeholder="https://...">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold text-white">Zapisz produkt</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-4">
                @if (session('success'))
                    <div class="alert alert-success shadow">{{ session('success') }}</div>
                @endif

                <div class="card border-0 rounded-0 shadow">
                    <div class="card-header border-0 rounded-0 fw-bold bg-primary text-white">Twoje śledzone produkty</div>

                    <div class="card border-0 rounded-0 shadow-sm mb-4">
                        <div class="card-body p-3">
                            <form action="{{ route('products.index') }}" method="GET" id="formularz-filtrowania">
                                <div class="row g-2 align-items-center">

                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <input type="text" name="wyszukaj" class="form-control" placeholder="Szukaj produktu..." value="{{ request('wyszukaj') }}">
                                            <button class="btn btn-primary" type="submit" title="Szukaj">
                                                🔍
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <select name="sortowanie" class="form-select text-muted" onchange="document.getElementById('formularz-filtrowania').submit();">
                                            <option value="najnowsze" {{ request('sortowanie') == 'najnowsze' ? 'selected' : '' }}>Od najnowszego</option>
                                            <option value="najstarsze" {{ request('sortowanie') == 'najstarsze' ? 'selected' : '' }}>Od najstarszego</option>
                                            <option value="cel_rosnaco" {{ request('sortowanie') == 'cel_rosnaco' ? 'selected' : '' }}>Cena docelowa (rosnąco)</option>
                                            <option value="aktualna_rosnaco" {{ request('sortowanie') == 'aktualna_rosnaco' ? 'selected' : '' }}>Cena aktualna (najtańsze)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 text-end">
                                        <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#zaawansowane-filtry" aria-expanded="false">
                                            Filtry ⚙️
                                        </button>
                                    </div>
                                </div>

                                <div class="collapse {{ request()->anyFilled(['sklepy_od', 'sklepy_do', 'cel_od', 'cel_do', 'aktualna_od', 'aktualna_do']) ? 'show' : '' }} mt-3" id="zaawansowane-filtry">
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

                    <div class="card-body">
                        @forelse($products as $product)
                            <div id="product-{{ $product->id }}" class="mb-5 border-bottom pb-4" style="scroll-margin-top: 20px;">

                                @php
                                    $aktualneCeny = $product->urls
                                        ->map(function ($modelLinku) {
                                            return $modelLinku->priceHistories->sortByDesc('created_at')->first()->price ?? null;
                                        })->filter();

                                    $najnizszaCena = $aktualneCeny->min();
                                @endphp

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
                                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none ms-1 fw-bold align-baseline" data-bs-toggle="modal" data-bs-target="#editProductModal-{{ $product->id }}">
                                                    Dodaj cenę docelową
                                                </button>
                                            @endif

                                            <span class="mx-2">|</span>

                                            <strong>Aktualnie najtaniej:</strong>
                                            @if ($najnizszaCena)
                                                <span class="{{ $product->target_price && $najnizszaCena <= $product->target_price ? 'text-success fw-bold' : 'text-primary fw-bold' }}">
                                                    {{ $najnizszaCena }} PLN
                                                </span>
                                            @else
                                                Brak danych
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-1">
                                        <button class="btn btn-sm btn-outline-primary toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $product->id }}" aria-expanded="false" aria-controls="collapse-{{ $product->id }}">
                                            <span class="text-expand">Rozwiń 🔽</span>
                                            <span class="text-collapse">Zwiń 🔼</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="collapse" id="collapse-{{ $product->id }}">

                                    <div class="d-flex justify-content-end align-items-center gap-2 pt-3 mb-4">
                                        <form action="{{ route('products.favourite', $product->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $product->is_favourite ? 'btn-warning border-warning text-dark' : 'btn-outline-warning text-dark' }}" title="Ulubione">
                                                ⭐
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editProductModal-{{ $product->id }}">
                                            Edytuj produkt ⚙️
                                        </button>

                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="m-0" onsubmit="return confirm('Czy na pewno chcesz przestać śledzić TEN PRODUKT?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Przestań śledzić ✖️</button>
                                        </form>
                                    </div>

                                    <form action="{{ route('products.urls.store', $product->id) }}" method="POST" class="d-flex gap-2 mb-4 mt-3">
                                        @csrf
                                        <input type="url" name="url" class="form-control form-control-sm bg-light" placeholder="Wklej link z kolejnego sklepu..." required>
                                        <button type="submit" class="btn btn-sm btn-outline-primary whitespace-nowrap text-nowrap">Dodaj sklep</button>
                                    </form>

                                    @foreach ($product->urls as $modelLinku)
                                        <div class="mt-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1 bg-light p-2 rounded border border-light">
                                                <div class="small">
                                                    <strong>{{ $modelLinku->store_name }}</strong> |
                                                    <a href="{{ $modelLinku->url }}" target="_blank" class="text-decoration-none">Sklep &rarr;</a>
                                                </div>

                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editUrlModal-{{ $modelLinku->id }}">
                                                        Edytuj sklep ⚙️
                                                    </button>

                                                    <form action="{{ route('urls.destroy', $modelLinku->id) }}" method="POST" class="m-0" onsubmit="return confirm('Przestać śledzić ten sklep?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Przestań śledzić ✖️</button>
                                                    </form>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="editUrlModal-{{ $modelLinku->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('urls.update', $modelLinku->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edytuj dane sklepu</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Adres www sklepu (host)</label>
                                                                    <input type="text" name="store_name" class="form-control" value="{{ $modelLinku->store_name }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Link do produktu</label>
                                                                    <input type="url" name="url" class="form-control" value="{{ $modelLinku->url }}" required>
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

                                    @php
                                        // Zbieranie danych społeczności z bazy
                                        $historiaSpolecznosci = collect();
                                        foreach($product->urls as $modelLinku) {
                                            $stareCeny = \App\Models\PriceHistory::whereHas('url', function($zapytanie) use ($modelLinku) {
                                                    $zapytanie->where('url', $modelLinku->url);
                                                })
                                                ->where('created_at', '<', $modelLinku->created_at)
                                                ->get();
                                            $historiaSpolecznosci = $historiaSpolecznosci->merge($stareCeny);
                                        }

                                        // Łączenie wszystkich dat (bez limitu ->take() !)
                                        $datyWszystkieCarbon = $product->urls->flatMap(function($modelLinku) {
                                            return $modelLinku->priceHistories->pluck('created_at');
                                        })->merge($historiaSpolecznosci->pluck('created_at'))
                                        ->unique(function($dataZestawienia) {
                                            return $dataZestawienia->format('Y-m-d');
                                        })
                                        ->sortBy(function($dataZestawienia) {
                                            return $dataZestawienia->timestamp;
                                        })
                                        ->values();

                                        // Format przyjazny dla podpisu osi X (np. 15.05.2026)
                                        $wszystkieDatyFormat = $datyWszystkieCarbon->map->format('d.m.Y');

                                        // Format techniczny do porównywania kalendarzy (np. 2026-05-15)
                                        $wszystkieDatyISO = $datyWszystkieCarbon->map->format('Y-m-d');
                                    @endphp

                                    @if($product->urls->count() > 0)
                                        <div class="mt-4 mb-3 p-3 bg-light rounded border border-light d-flex flex-column flex-md-row justify-content-between gap-3">

                                            <div>
                                                <div class="fw-bold small text-muted mb-2 px-1">Wyświetlane na wykresie:</div>
                                                <div class="d-flex flex-wrap gap-2 px-1">
                                                    @foreach($product->urls as $indeks => $modelLinku)
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input przelacznik-sklepu-{{ $product->id }}" type="checkbox" role="switch"
                                                                id="przelacznik-{{ $modelLinku->id }}" data-index="{{ $indeks }}" checked>
                                                            <label class="form-check-label small" for="przelacznik-{{ $modelLinku->id }}">
                                                                {{ $modelLinku->store_name }}
                                                            </label>
                                                        </div>
                                                    @endforeach

                                                    @if($historiaSpolecznosci->isNotEmpty())
                                                        <div class="form-check form-switch border-start ps-4 ms-2">
                                                            <input class="form-check-input przelacznik-sklepu-{{ $product->id }}" type="checkbox" role="switch"
                                                                id="przelacznik-spolecznosc-{{ $product->id }}" data-index="{{ $product->urls->count() }}" checked>
                                                            <label class="form-check-label small text-muted fw-bold" for="przelacznik-spolecznosc-{{ $product->id }}">
                                                                Dane archiwalne od innych użytkowników
                                                            </label>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div>
                                                <div class="fw-bold small text-muted mb-2 px-1">Zakres dat:</div>
                                                <div class="d-flex align-items-center gap-2 px-1">
                                                    <input type="date" id="data-start-{{ $product->id }}" class="form-control form-control-sm text-muted" style="width: 130px;">
                                                    <span class="text-muted small">-</span>
                                                    <input type="date" id="data-end-{{ $product->id }}" class="form-control form-control-sm text-muted" style="width: 130px;">
                                                </div>
                                            </div>

                                        </div>

                                        <div class="mb-4 bg-white p-2 border-0 rounded shadow-sm" style="height: 320px; width: 100%;">
                                            <canvas id="wykres-produktu-{{ $product->id }}"></canvas>
                                        </div>

                                        @if($aktualneCeny->isEmpty())
                                            <div class="mb-4 text-center bg-light p-3 rounded border border-light">
                                                <form action="{{ route('products.check-prices', $product->id) }}" method="POST" class="d-inline formularz-pobierania-cen">
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

                                        <script>
                                            document.addEventListener("DOMContentLoaded", function() {
                                                const kontekstWykresu = document.getElementById('wykres-produktu-{{ $product->id }}').getContext('2d');

                                                // Absolutnie wszystkie dane (przekazane z PHP)
                                                const wszystkieEtykiety = {!! json_encode($wszystkieDatyFormat) !!};
                                                const wszystkieDatyISO = {!! json_encode($wszystkieDatyISO) !!};
                                                const cenaDocelowa = {!! json_encode($product->target_price) !!};
                                                const oryginalneZbiory = [];

                                                const koloryWykresu = [
                                                    'rgb(75, 192, 192)', 'rgb(54, 162, 235)', 'rgb(255, 159, 64)',
                                                    'rgb(153, 102, 255)', 'rgb(255, 205, 86)', 'rgb(201, 203, 207)'
                                                ];

                                                @foreach($product->urls as $indeks => $modelLinku)
                                                    @php
                                                        $zmapowaneCeny = $wszystkieDatyFormat->map(function($dataEtykiety) use ($modelLinku) {
                                                            $wpis = $modelLinku->priceHistories->first(function($historia) use ($dataEtykiety) {
                                                                return $historia->created_at->format('d.m.Y') === $dataEtykiety;
                                                            });
                                                            return $wpis ? $wpis->price : null;
                                                        });
                                                    @endphp

                                                    oryginalneZbiory.push({
                                                        label: '{{ $modelLinku->store_name }}',
                                                        data: {!! json_encode($zmapowaneCeny->values()) !!},
                                                        borderColor: koloryWykresu[{{ $indeks }} % koloryWykresu.length],
                                                        backgroundColor: koloryWykresu[{{ $indeks }} % koloryWykresu.length].replace('rgb', 'rgba').replace(')', ', 0.1)'),
                                                        tension: 0,
                                                        fill: false
                                                    });
                                                @endforeach

                                                @if($historiaSpolecznosci->isNotEmpty())
                                                    @php
                                                        $cenySpolecznosciLista = [];
                                                        $sklepySpolecznosciLista = [];

                                                        foreach ($wszystkieDatyFormat as $dataEtykiety) {
                                                            $wpisyDnia = $historiaSpolecznosci->filter(function($historia) use ($dataEtykiety) {
                                                                return $historia->created_at->format('d.m.Y') === $dataEtykiety;
                                                            });

                                                            if ($wpisyDnia->isNotEmpty()) {
                                                                $najtanszyWpisDnia = $wpisyDnia->sortBy('price')->first();
                                                                $cenySpolecznosciLista[] = $najtanszyWpisDnia->price;
                                                                $sklepySpolecznosciLista[] = $najtanszyWpisDnia->url->store_name ?? 'Nieznany sklep';
                                                            } else {
                                                                $cenySpolecznosciLista[] = null;
                                                                $sklepySpolecznosciLista[] = null;
                                                            }
                                                        }
                                                    @endphp

                                                    oryginalneZbiory.push({
                                                        label: 'Dane innych użytkowników',
                                                        data: {!! json_encode($cenySpolecznosciLista) !!},
                                                        sklepy: {!! json_encode($sklepySpolecznosciLista) !!},
                                                        borderColor: 'rgba(108, 117, 125, 0.6)',
                                                        borderDash: [5, 5],
                                                        backgroundColor: 'rgba(108, 117, 125, 0.1)',
                                                        tension: 0,
                                                        fill: false
                                                    });
                                                @endif

                                                if (cenaDocelowa) {
                                                    oryginalneZbiory.push({
                                                        label: 'Cena docelowa',
                                                        data: Array(wszystkieEtykiety.length).fill(cenaDocelowa),
                                                        borderColor: 'rgb(255, 99, 132)',
                                                        borderWidth: 2,
                                                        borderDash: [5, 5],
                                                        pointRadius: 0,
                                                        hoverRadius: 0,
                                                        fill: false
                                                    });
                                                }

                                                // Inicjalizacja wykresu pusta, zaraz wypełni ją skrypt filtrujący
                                                const wykresInstancja = new Chart(kontekstWykresu, {
                                                    type: 'line',
                                                    data: { labels: [], datasets: oryginalneZbiory.map(zbior => Object.assign({}, zbior)) },
                                                    options: {
                                                        responsive: true,
                                                        maintainAspectRatio: false,
                                                        elements: {
                                                            point: {
                                                                radius: function(kontekst) {
                                                                    const ileWpisow = kontekst.dataset.data.filter(cena => cena !== null).length;
                                                                    return ileWpisow === 1 ? 4 : 0;
                                                                },
                                                                hitRadius: 10,
                                                                hoverRadius: 6
                                                            }
                                                        },
                                                        plugins: {
                                                            tooltip: {
                                                                callbacks: {
                                                                    label: function(kontekstSygnalu) {
                                                                        let tekstEtykiety = kontekstSygnalu.dataset.label || '';
                                                                        if (tekstEtykiety) tekstEtykiety += ': ';

                                                                        if (kontekstSygnalu.parsed.y !== null) {
                                                                            tekstEtykiety += kontekstSygnalu.parsed.y + ' PLN';
                                                                            if (kontekstSygnalu.dataset.sklepy && kontekstSygnalu.dataset.sklepy[kontekstSygnalu.dataIndex]) {
                                                                                tekstEtykiety += ' (' + kontekstSygnalu.dataset.sklepy[kontekstSygnalu.dataIndex] + ')';
                                                                            }
                                                                        }
                                                                        return tekstEtykiety;
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

                                                const dataStartInput = document.getElementById('data-start-{{ $product->id }}');
                                                const dataEndInput = document.getElementById('data-end-{{ $product->id }}');

                                                // Ustawiamy domyślne daty - start wyświetlania na "ostatnie 30 wpisów"
                                                if (wszystkieDatyISO.length > 0) {
                                                    const domyslnyIndeks = Math.max(0, wszystkieDatyISO.length - 30);
                                                    dataStartInput.value = wszystkieDatyISO[domyslnyIndeks];
                                                    dataEndInput.value = wszystkieDatyISO[wszystkieDatyISO.length - 1];
                                                }

                                                // Funkcja przycinająca wykres "w locie" na podstawie kalendarzy
                                                function filtrujWykres() {
                                                    const start = dataStartInput.value;
                                                    const end = dataEndInput.value;

                                                    const przefiltrowaneIndeksy = [];
                                                    wszystkieDatyISO.forEach((iso, index) => {
                                                        if ((!start || iso >= start) && (!end || iso <= end)) {
                                                            przefiltrowaneIndeksy.push(index);
                                                        }
                                                    });

                                                    wykresInstancja.data.labels = przefiltrowaneIndeksy.map(i => wszystkieEtykiety[i]);

                                                    wykresInstancja.data.datasets.forEach((dataset, j) => {
                                                        if (oryginalneZbiory[j].label === 'Cena docelowa') {
                                                            dataset.data = Array(przefiltrowaneIndeksy.length).fill(cenaDocelowa);
                                                        } else {
                                                            dataset.data = przefiltrowaneIndeksy.map(i => oryginalneZbiory[j].data[i]);
                                                            if (oryginalneZbiory[j].sklepy) {
                                                                dataset.sklepy = przefiltrowaneIndeksy.map(i => oryginalneZbiory[j].sklepy[i]);
                                                            }
                                                        }
                                                    });

                                                    wykresInstancja.update();
                                                }

                                                // Aktualizuj na bieżąco, gdy użytkownik wybierze inną datę
                                                dataStartInput.addEventListener('change', filtrujWykres);
                                                dataEndInput.addEventListener('change', filtrujWykres);

                                                // Wywołujemy od razu, by przyciąć do domyślnych 30 dni
                                                filtrujWykres();

                                                // Nasłuchiwacz do włączania/wyłączania linii poszczególnych sklepów
                                                document.querySelectorAll('.przelacznik-sklepu-{{ $product->id }}').forEach(przelacznik => {
                                                    przelacznik.addEventListener('change', function() {
                                                        const indeksZestawu = this.dataset.index;
                                                        wykresInstancja.setDatasetVisibility(indeksZestawu, this.checked);
                                                        wykresInstancja.update();
                                                    });
                                                });
                                            });
                                        </script>
                                    @endif

                                </div>

                                <div class="modal fade" id="editProductModal-{{ $product->id }}" tabindex="-1" aria-hidden="true">
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
                            <div class="text-center p-5 text-muted">
                                <h5>Nie śledzisz jeszcze żadnych produktów</h5>
                                <p>Skorzystaj z formularza po lewej stronie, aby zacząć.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-2">
                <div class="card position-sticky rounded-0 shadow border-0" style="top: 20px;">
                    <div class="card-header fw-bold bg-warning bg-opacity-25 text-dark rounded-0 border-0 d-flex align-items-center gap-2">
                        ⭐ Ulubione
                    </div>
                    <div class="card-body p-0">
                        @php
                            $ulubioneProdukty = $products->where('is_favourite', true);
                        @endphp

                        @if($ulubioneProdukty->count() > 0)
                            <div class="list-group list-group-flush rounded-bottom">
                                @foreach($ulubioneProdukty as $ulubionyModel)
                                    @php
                                        $ulubioneAktualneCeny = $ulubionyModel->urls->map(function ($modelLinku) {
                                            return $modelLinku->priceHistories->sortByDesc('created_at')->first()->price ?? null;
                                        })->filter();

                                        $ulubionaNajnizszaCena = $ulubioneAktualneCeny->min();
                                    @endphp

                                    <a href="#product-{{ $ulubionyModel->id }}" class="list-group-item list-group-item-action py-2">
                                        <div class="fw-semibold text-primary mb-1" style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $ulubionyModel->name }}">
                                            {{ $ulubionyModel->name }}
                                        </div>

                                        <div class="d-flex flex-column text-muted" style="font-size: 0.75rem;">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Cel:</span>
                                                <span>{{ $ulubionyModel->target_price ? $ulubionyModel->target_price . ' PLN' : '-' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Aktualnie:</span>
                                                @if ($ulubionaNajnizszaCena)
                                                    <span class="{{ $ulubionyModel->target_price && $ulubionaNajnizszaCena <= $ulubionyModel->target_price ? 'text-success fw-bold' : 'text-dark fw-bold' }}">
                                                        {{ $ulubionaNajnizszaCena }} PLN
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
                            <div class="p-4 text-muted small text-center" style="font-size: 0.8rem;">
                                Brak ulubionych. Zaznacz gwiazdkę przy ważnych dla Ciebie produktach!
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let otwarteAkordeony = JSON.parse(sessionStorage.getItem('openAccordions') || '[]');

            otwarteAkordeony.forEach(function(id) {
                let element = document.getElementById(id);
                if (element) {
                    element.classList.add('show');
                    let przycisk = document.querySelector(`[data-bs-target="#${id}"]`);
                    if (przycisk) przycisk.setAttribute('aria-expanded', 'true');
                }
            });

            document.querySelectorAll('.collapse').forEach(function(zwijanyElement) {
                zwijanyElement.addEventListener('shown.bs.collapse', function(zdarzenie) {
                    let listaOtwartych = JSON.parse(sessionStorage.getItem('openAccordions') || '[]');
                    if (!listaOtwartych.includes(zdarzenie.target.id)) {
                        listaOtwartych.push(zdarzenie.target.id);
                        sessionStorage.setItem('openAccordions', JSON.stringify(listaOtwartych));
                    }
                });

                zwijanyElement.addEventListener('hidden.bs.collapse', function(zdarzenie) {
                    let listaOtwartych = JSON.parse(sessionStorage.getItem('openAccordions') || '[]');
                    listaOtwartych = listaOtwartych.filter(id => id !== zdarzenie.target.id);
                    sessionStorage.setItem('openAccordions', JSON.stringify(listaOtwartych));
                });
            });

            let pozycjaEkranu = sessionStorage.getItem('scrollpos');
            if (pozycjaEkranu) {
                setTimeout(function() {
                    window.scrollTo(0, parseInt(pozycjaEkranu));
                    sessionStorage.removeItem('scrollpos');
                }, 50);
            }
        });

        window.addEventListener("beforeunload", function () {
            sessionStorage.setItem('scrollpos', window.scrollY);
        });
    </script>

<div id="ekran-ladowania" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; color: white;">
    <div class="spinner-border text-light mb-4" role="status" style="width: 4rem; height: 4rem;"></div>
    <h4 class="fw-bold">Trwa komunikacja ze sklepami...</h4>
    <p class="text-light opacity-75 mt-2">To może potrwać kilkanaście sekund. Proszę nie zamykać okna.</p>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.formularz-pobierania-cen').forEach(formularz => {
            formularz.addEventListener('submit', function() {
                document.getElementById('ekran-ladowania').style.display = 'flex';
            });
        });
    });
</script>
@endsection