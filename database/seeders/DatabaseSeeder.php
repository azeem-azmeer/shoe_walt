<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
public function run(): void
{
    User::updateOrCreate(
        ['email' => 'azeemazmeer4@gmail.com'],   // match row
        [
            'name' => 'Admin',
            'password' => Hash::make('admin123'), // rotate later
            'role' => 'admin',
            'email_verified_at' => now(),         // passes 'verified' middleware
        ]
    );
}
}
