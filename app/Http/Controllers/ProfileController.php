<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        // Walidacja danych
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed', // confirmed wymusza podanie pola password_confirmation
        ]);

        // Aktualizacja podstawowych danych
        $user->name = $request->name;
        $user->email = $request->email;

        // Jeśli użytkownik wpisał nowe hasło, też je zmieniamy
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Twoje dane zostały zaktualizowane!');
    }

    public function destroy(Request $request)
{
    // 1. Wymagamy podania prawidłowego hasła do konta
    $request->validate([
        'delete_password' => 'required|current_password',
    ], [
        'delete_password.current_password' => 'Podane hasło jest nieprawidłowe.'
    ]);

    $user = auth()->user();

    // 2. Wylogowujemy użytkownika
    Auth::logout();

    // 3. Usuwamy użytkownika z bazy
    // Jeśli w migracjach masz onDelete('cascade') przy produktach, Laravel wyczyści wszystko za Ciebie
    $user->delete();

    // 4. Unieważniamy sesję i odświeżamy token CSRF dla bezpieczeństwa
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // 5. Przekierowujemy na stronę główną
    return redirect('/')->with('success', 'Twoje konto oraz wszystkie powiązane dane zostały trwale usunięte.');
}
}