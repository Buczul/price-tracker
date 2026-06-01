<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // Wyświetla listę produktów zalogowanego użytkownika i formularz
    public function index(Request $zadanie)
{
    // Poczatek zapytania: pobieramy tylko aktywne produkty zalogowanego użytkownika
    $zapytanie = Product::where('user_id', auth()->id())
        ->whereNull('end_of_tracking_at');

    // 1. FILTROWANIE BAZODANOWE (Eager Loading relacji dla optymalizacji zapytania N+1)
    $zapytanie->with(['urls' => function($filtrLinkow) {
        $filtrLinkow->whereNull('end_of_tracking_at')->with('priceHistories');
    }]);

    // Wyszukiwanie tekstu po nazwie produktu
    if ($zadanie->filled('wyszukaj')) {
        $zapytanie->where('name', 'like', '%' . $zadanie->wyszukaj . '%');
    }

    // Filtr: Cena docelowa (Od)
    if ($zadanie->filled('cel_od')) {
        $zapytanie->where('target_price', '>=', $zadanie->cel_od);
    }

    // Filtr: Cena docelowa (Do)
    if ($zadanie->filled('cel_do')) {
        $zapytanie->where('target_price', '<=', $zadanie->cel_do);
    }

    // Pobieramy kolekcję z bazy danych do dalszego filtrowania dynamicznego
    $produkty = $zapytanie->get();

    // 2. MAPOWANIE KOLEKCJI (Wyliczamy ceny i statystyki "w locie" dla każdego produktu)
    $produkty = $produkty->map(function ($produkt) {
        $cenyObecne = $produkt->urls->map(function ($modelLinku) {
            return $modelLinku->priceHistories->sortByDesc('created_at')->first()->price ?? null;
        })->filter();

        // Przypisujemy tymczasowe właściwości do obiektu modelu
        $produkt->wyliczana_cena_aktualna = $cenyObecne->min();
        $produkt->liczba_sklepow = $produkt->urls->count();

        return $produkt;
    });

    // 3. ZAAWANSOWANE FILTROWANIE KOLEKCJI (W pamięci PHP)

    // Filtr: Liczba sklepów (Od)
    if ($zadanie->filled('sklepy_od')) {
        $produkty = $produkty->where('liczba_sklepow', '>=', $zadanie->sklepy_od);
    }

    // Filtr: Liczba sklepów (Do)
    if ($zadanie->filled('sklepy_do')) {
        $produkty = $produkty->where('liczba_sklepow', '<=', $zadanie->sklepy_do);
    }

    // Filtr: Cena aktualna (Od)
    if ($zadanie->filled('aktualna_od')) {
        $produkty = $produkty->where('wyliczana_cena_aktualna', '>=', $zadanie->aktualna_od);
    }

    // Filtr: Cena aktualna (Do)
    if ($zadanie->filled('aktualna_do')) {
        $produkty = $produkty->where('wyliczana_cena_aktualna', '<=', $zadanie->aktualna_do);
    }

    // 4. SORTOWANIE KOLEKCJI
    switch ($zadanie->sortowanie) {
        case 'najstarsze':
            $produkty = $produkty->sortBy('created_at');
            break;

        case 'cel_rosnaco':
            // Produkty bez ceny docelowej lądują na samym końcu
            $produkty = $produkty->sortBy(function ($produkt) {
                return $produkt->target_price ?? PHP_INT_MAX;
            });
            break;

        case 'aktualna_rosnaco':
            // Produkty bez pobranej ceny lądują na samym końcu
            $produkty = $produkty->sortBy(function ($produkt) {
                return $produkt->wyliczana_cena_aktualna ?? PHP_INT_MAX;
            });
            break;

        case 'najnowsze':
        default:
            $produkty = $produkty->sortByDesc('created_at');
            break;
    }

    // Zwracamy widok z ostatecznie przefiltrowaną i posortowaną kolekcją
    return view('products.index', [
        'products' => $produkty
    ]);
}

    // Zapisuje nowy produkt i link z formularza
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_price' => 'nullable|numeric|min:0',
            'url' => 'required|url',
        ]);

        // Tworzenie produktu powiązanego z użytkownikiem
        $product = auth()->user()->products()->create([
            'name' => $request->name,
            'target_price' => $request->target_price,
        ]);

        // Wyciąganie czystej domeny z URL
        $host = parse_url($request->url, PHP_URL_HOST);

        // Dodanie pierwszego linku z domeną jako nazwą sklepu
        $product->urls()->create([
            'url' => $request->url,
            'store_name' => $host,
        ]);

        return back()->with('success', 'Produkt został dodany do śledzenia!');
    }

    public function addUrl(Request $request, \App\Models\Product $product)
    {
        // Sprawdzamy uprawnienia
        if ($product->user_id !== \Illuminate\Support\Facades\Auth::id()) abort(403);

        $request->validate([
            'url' => 'required|url',
        ]);

        // Wyciąganie czystej domeny z URL dokładnie w ten sam sposób
        $host = parse_url($request->url, PHP_URL_HOST);

        // Dodanie nowego linku przez relację
        $product->urls()->create([
            'url' => $request->url,
            'store_name' => $host,
        ]);

        return back()->with('success', 'Kolejny sklep dodany do śledzenia!');
    }

    // --- EDYCJA I USUWANIE CAŁYCH PRODUKTÓW ---

    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== \Illuminate\Support\Facades\Auth::id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'target_price' => 'nullable|numeric|min:0'
        ]);

        $product->update([
            'name' => $request->name,
            'target_price' => $request->target_price
        ]);

        return back()->with('success', 'Produkt został zaktualizowany!');
    }

    public function destroy(\App\Models\Product $product)
    {
        // Zabezpieczenie: użytkownik usuwa SWÓJ produkt
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        // Omijamy blokadę $fillable przez bezpośrednie przypisanie
        $product->end_of_tracking_at = now();
        $product->save();

        return back()->with('success', 'Produkt został usunięty z Twojej listy.');
    }

    // --- EDYCJA I USUWANIE LINKÓW (SKLEPÓW) ---

    public function updateUrl(Request $request, \App\Models\ProductUrl $url)
    {
        // Sprawdzenie czy link należy do produktu, który należy do zalogowanego usera
        if ($url->product->user_id !== \Illuminate\Support\Facades\Auth::id()) abort(403);

        $request->validate([
            'store_name' => 'required|string|max:255',
            'url' => 'required|url',
        ]);

        $url->update([
            'store_name' => $request->store_name,
            'url' => $request->url,
        ]);

        return back()->with('success', 'Dane sklepu zostały zaktualizowane!');
    }

    public function deleteUrl(\App\Models\ProductUrl $url)
    {
        if ($url->product->user_id !== \Illuminate\Support\Facades\Auth::id()) abort(403);

        // Omijamy blokadę $fillable przez bezpośrednie przypisanie
        $url->end_of_tracking_at = now();
        $url->save();

        return back()->with('success', 'Przestałeś śledzić ten sklep. Historia cen została zachowana dla społeczności.');
    }

    public function toggleFavourite(\App\Models\Product $product)
    {
        if ($product->user_id !== auth()->id()) abort(403);

        // Przełączamy wartość na przeciwną (z false na true lub z true na false)
        $product->is_favourite = !$product->is_favourite;
        $product->save();

        return back()->with('success', 'Zaktualizowano status ulubionych!');
    }

    public function sprawdzCenyRecznie(\App\Models\Product $produkt)
    {
        // Weryfikacja właściciela
        if ($produkt->user_id !== auth()->id()) abort(403);

        $kluczApi = env('SCRAPER_API_KEY');
        if (!$kluczApi) {
            return back()->with('error', 'Brak skonfigurowanego klucza ScraperAPI.');
        }

        foreach ($produkt->urls as $modelLinku) {
            try {
                $adresApi = "http://api.scraperapi.com?api_key={$kluczApi}&url=" . urlencode($modelLinku->url);
                $odpowiedz = \Illuminate\Support\Facades\Http::timeout(60)->get($adresApi);

                if ($odpowiedz->successful()) {
                    $przeszukiwacz = new \Symfony\Component\DomCrawler\Crawler($odpowiedz->body());
                    $znalezionaCena = null;
                    $skrypty = $przeszukiwacz->filter('script[type="application/ld+json"]')->extract(['_text']);

                    foreach ($skrypty as $skrypt) {
                        $dane = json_decode($skrypt, true);
                        if (!$dane) continue;

                        $elementyDoPrzeszukania = isset($dane['@graph']) ? $dane['@graph'] : (isset($dane['@type']) ? [$dane] : $dane);

                        if (is_array($elementyDoPrzeszukania)) {
                            foreach ($elementyDoPrzeszukania as $elementJson) {
                                $czyToProdukt = isset($elementJson['@type']) && (
                                    $elementJson['@type'] === 'Product' ||
                                    (is_array($elementJson['@type']) && in_array('Product', $elementJson['@type']))
                                );

                                if ($czyToProdukt) {
                                    if (isset($elementJson['offers']['price'])) {
                                        $znalezionaCena = $elementJson['offers']['price'];
                                        break 2;
                                    } elseif (isset($elementJson['offers'][0]['price'])) {
                                        $znalezionaCena = $elementJson['offers'][0]['price'];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    if (!$znalezionaCena) {
                        if (preg_match('/"price"\s*:\s*([\d\.]+)/', $odpowiedz->body(), $dopasowania)) {
                            $znalezionaCena = $dopasowania[1];
                        }
                    }

                    // Zapisujemy nową cenę do bazy, bez powiadomień mailowych (to tylko pierwszy zrzut)
                    if ($znalezionaCena) {
                        \App\Models\PriceHistory::create([
                            'product_url_id' => $modelLinku->id,
                            'price' => $znalezionaCena
                        ]);
                    }
                }
            } catch (\Exception $wyjatek) {
                // Ciche pominięcie błędu pojedynczego linku (np. timeout), przechodzi do kolejnego sklepu
                continue;
            }
        }

        return back()->with('success', 'Pierwsze ceny zostały pobrane i dodane do wykresu!');
    }
}