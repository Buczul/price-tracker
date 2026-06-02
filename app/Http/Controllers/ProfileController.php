<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Pokazuje formularz edycji profilu.
     */
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Aktualizuje dane profilu użytkownika.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required|current_password',
            'notify_via_email' => 'nullable|boolean',
        ];

        if ($request->filled('new_password')) {
            $rules['new_password'] = 'required|string|min:8|confirmed';
        }

        $validatedData = $request->validate($rules, [
            'current_password.current_password' => 'Podane aktualne hasło jest nieprawidłowe.'
        ]);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->notify_via_email = $request->has('notify_via_email');

        if ($request->filled('new_password')) {
            $user->password = Hash::make($validatedData['new_password']);
        }

        $user->save();

        return back()->with('success', 'Ustawienia profilu zostały zaktualizowane!');
    }

    /**
     * Trwale usuwa konto użytkownika.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password_to_delete' => 'required|current_password',
        ], [
            'password_to_delete.current_password' => 'Podane hasło jest nieprawidłowe.'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Twoje konto oraz wszystkie powiązane dane zostały trwale usunięte.');
    }
}