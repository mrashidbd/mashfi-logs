<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Surveyor User
        User::factory()->create([
            'name' => 'Surveyor One',
            'email' => 'surveyor@example.com',
            'password' => Hash::make('password'),
            'role' => 'surveyor',
        ]);

        // Buyer User
        User::factory()->create([
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
            'role' => 'buyer',
        ]);
    }
}
