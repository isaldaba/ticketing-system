<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@ticketing.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
        ]);

        // Staff user
        User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@ticketing.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'staff',
        ]);
    }
}
