<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Wypełnia bazę danych początkowymi danymi.
     */
    public function run(): void
    {
        // Tworzenie testowego konta użytkownika
        User::updateOrCreate(
            ['email' => 'test@test.com'],
            [
                'name'              => 'test',
                'password'          => Hash::make('test1234'),
                'email_verified_at' => now(),
                'is_admin'          => false,
            ]
        );

        // Tworzenie testowego konta administratora
        User::updateOrCreate(
            ['email' => 'admintest@test.pl'],
            [
                'name'              => 'admintest',
                'password'          => Hash::make('admintest1'),
                'email_verified_at' => now(),
                'is_admin'          => true,
            ]
        );
    }
}