<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductUrl;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Zaktualizowana, angielska nazwa metody
    private function getModelClass($tabela)
    {
        $mapowanie = [
            'uzytkownicy' => User::class,
            'produkty' => Product::class,
            'linki_sklepow' => ProductUrl::class,
            'historie_cen' => PriceHistory::class,
            'powiadomienia' => \Illuminate\Notifications\DatabaseNotification::class,
        ];

        if (!array_key_exists($tabela, $mapowanie)) {
            abort(404, 'Table does not exist in the admin system.');
        }

        return $mapowanie[$tabela];
    }

    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'products' => Product::count(),
            'urls' => ProductUrl::count(),
            'histories' => PriceHistory::count(),
        ];

        $topStores = ProductUrl::select('store_name', DB::raw('count(*) as total'))
            ->groupBy('store_name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $recentProducts = Product::with('user')->latest()->take(5)->get();

        $tabela = null;

        return view('admin.dashboard', compact('stats', 'topStores', 'recentProducts', 'tabela'));
    }

    public function index($tabela)
    {
        // Użycie nowej nazwy metody
        $klasaModelu = $this->getModelClass($tabela);
        $wiersze = $klasaModelu::paginate(50);
        return view('admin.index', compact('wiersze', 'tabela'));
    }

    public function edit($tabela, $id)
    {
        // Użycie nowej nazwy metody
        $klasaModelu = $this->getModelClass($tabela);
        $wiersz = $klasaModelu::findOrFail($id);
        return view('admin.edit', compact('wiersz', 'tabela'));
    }

    public function update(Request $zadanie, $tabela, $id)
    {
        // Użycie nowej nazwy metody
        $klasaModelu = $this->getModelClass($tabela);
        $wiersz = $klasaModelu::findOrFail($id);

        $daneDoAktualizacji = $zadanie->except(['_token', '_method', 'id', 'created_at', 'updated_at']);
        $wiersz->update($daneDoAktualizacji);

        return redirect()->route('admin.index', $tabela)->with('success', 'Rekord został zaktualizowany.');
    }

    public function destroy($tabela, $id)
    {
        // Użycie nowej nazwy metody
        $klasaModelu = $this->getModelClass($tabela);
        $wiersz = $klasaModelu::findOrFail($id);

        // HARD DELETE
        if (method_exists($wiersz, 'forceDelete')) {
            $wiersz->forceDelete();
        } else {
            $wiersz->delete();
        }

        return back()->with('success', 'Rekord bezpowrotnie usunięty z bazy danych.');
    }
}