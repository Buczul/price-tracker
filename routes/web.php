<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Wymuszenia logowania, żeby wejść pod adresy
Route::middleware('auth')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
});

Route::post('/products/{product}/urls', [App\Http\Controllers\ProductController::class, 'addUrl'])->name('products.urls.store');

// Trasy dla całych Produktów
Route::put('/products/{product}', [App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');

// Trasy dla pojedynczych Sklepów (Linków)
Route::put('/urls/{url}', [App\Http\Controllers\ProductController::class, 'updateUrl'])->name('urls.update');
Route::delete('/urls/{url}', [App\Http\Controllers\ProductController::class, 'deleteUrl'])->name('urls.destroy');

Route::post('/notifications/read', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return back();
})->middleware('auth')->name('notifications.read');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Główna strona zarządzania użytkownikami
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('users.index');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Główna strona panelu (Dashboard)
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

    // Dotychczasowa strona użytkowników
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('users.index');
});
