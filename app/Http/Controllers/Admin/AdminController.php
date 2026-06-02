<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductUrl;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class AdminController extends Controller
{
    /**
     * Zwraca klasę modelu odpowiadającą wybranej tabeli administracyjnej.
     */
    private function getModelClass(string $table): string
    {
        $mapping = [
            'uzytkownicy'   => User::class,
            'produkty'      => Product::class,
            'linki_sklepow' => ProductUrl::class,
            'historie_cen'  => PriceHistory::class,
            'powiadomienia' => DatabaseNotification::class,
        ];

        if (!array_key_exists($table, $mapping)) {
            abort(404, 'Tabela nie istnieje w systemie.');
        }

        return $mapping[$table];
    }

    /**
     * Wyświetla panel administracyjny wraz z podstawowymi statystykami systemu.
     */
    public function dashboard()
    {
        $stats = [
            'users'      => User::count(),
            'products'   => Product::count(),
            'urls'       => ProductUrl::count(),
            'histories'  => PriceHistory::count(),
        ];

        $topStores = ProductUrl::select('store_name', DB::raw('count(*) as total'))
            ->groupBy('store_name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $recentProducts = Product::with('user')
            ->latest()
            ->take(5)
            ->get();

        $table = null;

        return view('admin.dashboard', compact('stats', 'topStores', 'recentProducts', 'table'));
    }

    /**
     * Wyświetla listę rekordów wybranej tabeli z paginacją.
     */
    public function index(string $table)
    {
        $modelClass = $this->getModelClass($table);
        $rows = $modelClass::paginate(50);

        return view('admin.index', compact('rows', 'table'));
    }

    /**
     * Wyświetla formularz edycji wybranego rekordu.
     */
    public function edit(string $table, $id)
    {
        $modelClass = $this->getModelClass($table);
        $row = $modelClass::findOrFail($id);

        return view('admin.edit', compact('row', 'table'));
    }

    /**
     * Aktualizuje dane wybranego rekordu w bazie danych.
     */
    public function update(Request $request, string $table, $id)
    {
        $modelClass = $this->getModelClass($table);
        $row = $modelClass::findOrFail($id);

        $dataToUpdate = $request->except(['_token', '_method', 'id', 'created_at', 'updated_at']);
        $row->update($dataToUpdate);

        return redirect()->route('admin.index', $table)->with('success', 'Rekord został zaktualizowany.');
    }

    /**
     * Trwale usuwa wybrany rekord z bazy danych.
     */
    public function destroy(string $table, $id)
    {
        $modelClass = $this->getModelClass($table);
        $row = $modelClass::findOrFail($id);

        if (method_exists($row, 'forceDelete')) {
            $row->forceDelete();
        } else {
            $row->delete();
        }

        return back()->with('success', 'Rekord bezpowrotnie usunięty z bazy danych.');
    }
}