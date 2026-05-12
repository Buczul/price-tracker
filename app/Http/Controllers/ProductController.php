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
            'url' => 'required|url'
        ]);

        // Tworzymy produkt przypisany do usera
        $product = Product::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
        ]);

        // Podpinamy pod niego link (jako nazwę sklepu bierzemy domenę, np. allegro.pl)
        $product->urls()->create([
            'url' => $request->url,
            'store_name' => parse_url($request->url, PHP_URL_HOST)
        ]);

        return back()->with('success', 'Produkt został dodany do śledzenia!');
    }
}