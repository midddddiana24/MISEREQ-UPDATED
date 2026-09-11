<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (only if doesn't exist)
        User::updateOrCreate(
            ['username' => 'admin'], // Check if username exists
            [
                'first_name' => 'Admin',
                'middle_initial' => '',
                'last_name' => 'User',
                'office' => 'MIS Office',
                'designation' => 'System Administrator',
                'email' => 'admin@wvsu.edu.ph',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Create test user (only if doesn't exist)
        User::updateOrCreate(
            ['username' => 'juandc'], // Check if username exists
            [
                'first_name' => 'Juan',
                'middle_initial' => 'D',
                'last_name' => 'Dela Cruz',
                'office' => 'College of ICT',
                'designation' => 'Faculty',
                'email' => 'juan@wvsu.edu.ph',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        // Create another test user
        User::updateOrCreate(
            ['username' => 'maria'],
            [
                'first_name' => 'Maria',
                'middle_initial' => 'S',
                'last_name' => 'Santos',
                'office' => 'Registrar Office',
                'designation' => 'Staff',
                'email' => 'maria@wvsu.edu.ph',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        $this->command->info('✅ Users seeded successfully!');
        $this->command->info('📝 Test Credentials:');
        $this->command->line('   Admin - Username: admin, Password: admin123');
        $this->command->line('   User  - Username: juandc, Password: password123');
        $this->command->line('   User  - Username: maria, Password: password123');
    }
}
