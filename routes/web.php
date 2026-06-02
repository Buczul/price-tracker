<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;

Route::get('/', function () {
    return view('welcome');
});

// Włączenie tras autoryzacji oraz mechanizmu weryfikacji e-mail
Auth::routes(['verify' => true]);

// Wymuszenia logowania ORAZ weryfikacji e-maila (Wszystkie akcje zwykłego użytkownika)
Route::middleware(['auth', 'verified'])->group(function () {

    // Przeniesiona trasa /home do wnętrza grupy zabezpieczonej
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Główne widoki i dodawanie
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');

    // Trasy dla całych Produktów
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/products/{product}/favourite', [ProductController::class, 'toggleFavourite'])->name('products.favourite');

    // Trasy dla Sklepów (Linków)
    Route::post('/products/{product}/urls', [ProductController::class, 'addUrl'])->name('products.urls.store');
    Route::put('/urls/{url}', [ProductController::class, 'updateUrl'])->name('urls.update');
    Route::delete('/urls/{url}', [ProductController::class, 'deleteUrl'])->name('urls.destroy');

    // NOWA TRASA - Ręczne pobieranie cen
    Route::post('/products/{product}/check-prices', [ProductController::class, 'checkPricesManually'])->name('products.check-prices');

    // Powiadomienia
    Route::post('/notifications/read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read');

    // Profil użytkownika
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Grupa Administratora (wymaga logowania, weryfikacji e-mail ORAZ bycia adminem)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Strona główna panelu (Dashboard ze statystykami)
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Dynamiczne ścieżki do zarządzania bazą danych (wszystkie tabele)
    Route::get('/{table}', [AdminController::class, 'index'])->name('index');
    Route::get('/{table}/{id}/edit', [AdminController::class, 'edit'])->name('edit');
    Route::put('/{table}/{id}', [AdminController::class, 'update'])->name('update');
    Route::delete('/{table}/{id}', [AdminController::class, 'destroy'])->name('destroy');

});