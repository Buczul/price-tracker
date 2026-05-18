<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // Wyświetla listę produktów zalogowanego użytkownika i formularz
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pobieranie produktów z linkami ORAZ z historią cen ułożoną od najstarszej
        $products = $user->products()->with(['urls.priceHistories' => function($query) {
            $query->orderBy('created_at', 'asc');
        }])->get();

        return view('products.index', compact('products'));
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

        // Wyciąganie czystej domeny z URL (np. www.mediaexpert.pl)
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
           'target_price' => 'nullable|numeric|min:0' // Dodana walidacja
       ]);

       $product->update([
           'name' => $request->name,
           'target_price' => $request->target_price // Zapis do bazy
       ]);

       return back()->with('success', 'Produkt został zaktualizowany!');
   }

public function destroy(Product $product)
{
    if ($product->user_id !== \Illuminate\Support\Facades\Auth::id()) abort(403);

    $product->delete(); // Usunie produkt (a kaskada w bazie usunie jego linki i historie)
    return back()->with('success', 'Produkt został całkowicie usunięty.');
}

// --- EDYCJA I USUWANIE LINKÓW (SKLEPÓW) ---

public function updateUrl(Request $request, \App\Models\ProductUrl $url)
{
    // Sprawdzamy czy link należy do produktu, który należy do zalogowanego usera
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

    $url->delete();
    return back()->with('success', 'Sklep został usunięty ze śledzenia.');
}
}