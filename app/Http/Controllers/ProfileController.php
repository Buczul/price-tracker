<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // Wymagane dla Auth::logout() w metodzie destroy

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()
        ]);
    }

    public function update(Request $zadanie)
{
    /** @var \App\Models\User $uzytkownik */
    $uzytkownik = auth()->user();

    $reguly = [
        'nazwa' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $uzytkownik->id,
        'aktualne_haslo' => 'required|current_password',
        'notify_via_email' => 'nullable|boolean', // <-- ZMIANA TUTAJ
    ];

    if ($zadanie->filled('nowe_haslo')) {
        $reguly['nowe_haslo'] = 'required|string|min:8|confirmed';
    }

    $zwalidowaneDane = $zadanie->validate($reguly, [
        'aktualne_haslo.current_password' => 'Podane aktualne hasło jest nieprawidłowe.'
    ]);

    // Aktualizacja danych
    $uzytkownik->name = $zwalidowaneDane['nazwa'];
    $uzytkownik->email = $zwalidowaneDane['email'];

    // Zapisujemy preferencję powiadomień
    $uzytkownik->notify_via_email = $zadanie->has('notify_via_email'); // <-- ZMIANA TUTAJ

    if ($zadanie->filled('nowe_haslo')) {
        $uzytkownik->password = Hash::make($zwalidowaneDane['nowe_haslo']);
    }

    $uzytkownik->save();

    return back()->with('success', 'Ustawienia profilu zostały zaktualizowane!');
}

    public function destroy(Request $zadanie)
    {
        // 1. Wymagamy podania prawidłowego hasła do konta
        $zadanie->validate([
            'haslo_do_usuniecia' => 'required|current_password',
        ], [
            'haslo_do_usuniecia.current_password' => 'Podane hasło jest nieprawidłowe.'
        ]);

        $uzytkownik = auth()->user();

        // 2. Wylogowujemy użytkownika (teraz zadziała dzięki importowi Facades\Auth)
        Auth::logout();

        // 3. Usuwamy użytkownika z bazy
        $uzytkownik->delete();

        // 4. Unieważniamy sesję i odświeżamy token CSRF dla bezpieczeństwa
        $zadanie->session()->invalidate();
        $zadanie->session()->regenerateToken();

        // 5. Przekierowujemy na stronę główną
        return redirect('/')->with('success', 'Twoje konto oraz wszystkie powiązane dane zostały trwale usunięte.');
    }
}