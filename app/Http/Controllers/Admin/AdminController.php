<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductUrl;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // 1. Nowa metoda obsługująca Stronę Główną Panelu
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'products' => Product::count(),
            'urls' => ProductUrl::count(),
            'histories' => PriceHistory::count(),
        ];

        // Ranking 5 najpopularniejszych sklepów (grupowanie po domenie)
        $topStores = ProductUrl::select('store_name', DB::raw('count(*) as total'))
            ->groupBy('store_name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // 5 ostatnio dodanych produktów w całym systemie (wraz z informacją do kogo należą)
        $recentProducts = Product::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'topStores', 'recentProducts'));
    }

    // 2. Dotychczasowa metoda zarządzania użytkownikami
    public function index()
    {
        $users = User::withCount(['products', 'urls'])->get();
        return view('admin.users', compact('users'));
    }
}