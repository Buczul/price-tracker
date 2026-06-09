<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductUrl;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ProductController extends Controller
{
    /**
     * Wyświetla listę produktów zalogowanego użytkownika wraz z filtrowaniem i sortowaniem.
     */
    public function index(Request $request)
    {
        $query = Product::where('user_id', Auth::id())
            ->whereNull('end_of_tracking_at');

        // Eager Loading dla optymalizacji zapytania N+1
        $query->with(['urls' => function ($urlFilter) {
            $urlFilter->whereNull('end_of_tracking_at')->with('priceHistories');
        }]);

        // Wyszukiwanie po nazwie
        if ($request->filled('wyszukaj')) {
            $query->where('name', 'like', '%' . $request->wyszukaj . '%');
        }

        // Filtry ceny docelowej
        if ($request->filled('cel_od')) {
            $query->where('target_price', '>=', $request->cel_od);
        }

        if ($request->filled('cel_do')) {
            $query->where('target_price', '<=', $request->cel_do);
        }

        $products = $query->get();

        // Wyliczanie dynamicznych właściwości w locie dla każdego produktu
        $products = $products->map(function ($product) {
            $currentPrices = $product->urls->map(function ($urlModel) {
                return $urlModel->priceHistories->sortByDesc('created_at')->first()->price ?? null;
            })->filter();

            $product->calculated_current_price = $currentPrices->min();
            $product->stores_count = $product->urls->count();

            return $product;
        });

        // Zaawansowane filtrowanie kolekcji (w pamięci)
        if ($request->filled('sklepy_od')) {
            $products = $products->where('stores_count', '>=', $request->sklepy_od);
        }

        if ($request->filled('sklepy_do')) {
            $products = $products->where('stores_count', '<=', $request->sklepy_do);
        }

        if ($request->filled('aktualna_od')) {
            $products = $products->where('calculated_current_price', '>=', $request->aktualna_od);
        }

        if ($request->filled('aktualna_do')) {
            $products = $products->where('calculated_current_price', '<=', $request->aktualna_do);
        }

        // Sortowanie kolekcji
        switch ($request->sortowanie) {
            case 'najstarsze':
                $products = $products->sortBy('created_at');
                break;

            case 'cel_rosnaco':
                $products = $products->sortBy(function ($product) {
                    return $product->target_price ?? PHP_INT_MAX;
                });
                break;

            case 'aktualna_rosnaco':
                $products = $products->sortBy(function ($product) {
                    return $product->calculated_current_price ?? PHP_INT_MAX;
                });
                break;

            case 'najnowsze':
            default:
                $products = $products->sortByDesc('created_at');
                break;
        }

        return view('products.index', [
            'products' => $products
        ]);
    }

    /**
     * Zapisuje nowy produkt i jego pierwszy link.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'target_price' => 'nullable|numeric|min:0',
            'url'          => 'required|url',
        ]);

        $product = Auth::user()->products()->create([
            'name'         => $request->name,
            'target_price' => $request->target_price,
        ]);

        $hostDomain = parse_url($request->url, PHP_URL_HOST);

        $product->urls()->create([
            'url'        => $request->url,
            'store_name' => $hostDomain,
        ]);

        return back()->with('success', 'Produkt został dodany do śledzenia!');
    }

    /**
     * Dodaje kolejny link do istniejącego produktu.
     */
    public function addUrl(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'url' => 'required|url',
        ]);

        $hostDomain = parse_url($request->url, PHP_URL_HOST);

        $product->urls()->create([
            'url'        => $request->url,
            'store_name' => $hostDomain,
        ]);

        return back()->with('success', 'Kolejny sklep dodany do śledzenia!');
    }

    /**
     * Aktualizuje produkt.
     */
    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'target_price' => 'nullable|numeric|min:0'
        ]);

        $product->update([
            'name'         => $request->name,
            'target_price' => $request->target_price
        ]);

        return back()->with('success', 'Produkt został zaktualizowany!');
    }

    /**
     * Miękkie usuwanie produktu.
     */
    public function destroy(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $product->end_of_tracking_at = now();
        $product->save();

        return back()->with('success', 'Produkt został usunięty z Twojej listy.');
    }

    /**
     * Aktualizuje link do produktu.
     */
    public function updateUrl(Request $request, ProductUrl $url)
    {
        if ($url->product->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'store_name' => 'required|string|max:255',
            'url'        => 'required|url',
        ]);

        $url->update([
            'store_name' => $request->store_name,
            'url'        => $request->url,
        ]);

        return back()->with('success', 'Dane sklepu zostały zaktualizowane!');
    }

    /**
     * Miękkie usuwanie linku do produktu.
     */
    public function deleteUrl(ProductUrl $url)
    {
        if ($url->product->user_id !== Auth::id()) {
            abort(403);
        }

        $url->end_of_tracking_at = now();
        $url->save();

        return back()->with('success', 'Przestałeś śledzić ten sklep. Historia cen została zachowana dla społeczności.');
    }

    /**
     * Przełącza status ulubionych.
     */
    public function toggleFavourite(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $product->is_favourite = !$product->is_favourite;
        $product->save();

        return back()->with('success', 'Zaktualizowano status ulubionych!');
    }

    /**
     * Ręczne pobieranie cen dla linków powiązanych z produktem.
     */
    public function checkPricesManually(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $apiKey = env('SCRAPER_API_KEY');

        if (!$apiKey) {
            return back()->with('error', 'Brak skonfigurowanego klucza ScraperAPI.');
        }

        // 1. Zwiększamy tymczasowo limit czasu w PHP (dla pewności)
        set_time_limit(120);

        // 2. Odpalamy stoper
        $startTime = time();
        $timeoutReached = false;

        foreach ($product->urls as $urlModel) {

            // 3. Sprawdzamy czas przed każdym sklepem.
            // Jeśli minęło więcej niż 25 sekund, ewakuujemy się.
            if (time() - $startTime > 25) {
                $timeoutReached = true;
                break;
            }

            try {
                $apiUrl = "http://api.scraperapi.com?api_key={$apiKey}&url=" . urlencode($urlModel->url);
                $response = Http::timeout(60)->get($apiUrl);

                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    $foundPrice = null;

                    // Pobieranie ustrukturyzowanych danych JSON-LD
                    $scripts = $crawler->filter('script[type="application/ld+json"]')->extract(['_text']);

                    foreach ($scripts as $script) {
                        $data = json_decode($script, true);
                        if (!$data) {
                            continue;
                        }

                        $elementsToSearch = isset($data['@graph']) ? $data['@graph'] : (isset($data['@type']) ? [$data] : $data);

                        if (is_array($elementsToSearch)) {
                            foreach ($elementsToSearch as $jsonElement) {
                                $isProduct = isset($jsonElement['@type']) && (
                                    $jsonElement['@type'] === 'Product' ||
                                    (is_array($jsonElement['@type']) && in_array('Product', $jsonElement['@type']))
                                );

                                if ($isProduct) {
                                    if (isset($jsonElement['offers']['price'])) {
                                        $foundPrice = $jsonElement['offers']['price'];
                                        break 2;
                                    } elseif (isset($jsonElement['offers'][0]['price'])) {
                                        $foundPrice = $jsonElement['offers'][0]['price'];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    // Fallback na wyrażenie regularne, jeśli brakuje JSON-LD
                    if (!$foundPrice) {
                        if (preg_match('/"price"\s*:\s*([\d\.]+)/', $response->body(), $matches)) {
                            $foundPrice = $matches[1];
                        }
                    }

                    // Zapisanie nowej ceny w bazie
                    if ($foundPrice) {
                        PriceHistory::create([
                            'product_url_id' => $urlModel->id,
                            'price'          => $foundPrice
                        ]);
                    }
                }
            } catch (Exception $exception) {
                // Ciche pominięcie w razie błędu i przejście do kolejnego sklepu
                continue;
            }
        }

        // 4. Obsługa komunikatu na podstawie czasu wykonania
        if ($timeoutReached) {
            return back()->with('warning', 'Nie udało się teraz pobrać wszystkich aktualnych cen ze sklepów. Pamiętaj jednak, że system automatycznie zaktualizuje brakujące dane dzisiaj o 2:00 w nocy. Przepraszamy za te drobne utrudnienia!');
        }

        return back()->with('success', 'Pierwsze ceny zostały pobrane i dodane do wykresu!');
    }
}