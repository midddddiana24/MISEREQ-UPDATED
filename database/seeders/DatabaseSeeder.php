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
        // Create a test user
        User::create([
            'first_name' => 'John',
            'middle_initial' => 'D',
            'last_name' => 'Doe',
            'office' => 'College of ICT',
            'designation' => 'Student',
            'phone_local' => '1234',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);

        // Create an admin user
        User::create([
            'first_name' => 'Admin',
            'middle_initial' => 'A',
            'last_name' => 'User',
            'office' => 'Administration',
            'designation' => 'Administrator',
            'phone_local' => '5678',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'is_admin' => true,
        ]);
    }
}
