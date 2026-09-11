<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TempAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $email = 'admin+dev@local.test';
        // generate a short random password
        $password = 'Adm!'.substr(bin2hex(random_bytes(3)), 0, 8);

        if (User::where('email', $email)->exists()) {
            $this->command->info("Temp admin already exists: {$email}");

            return;
        }

        User::create([
            'name' => 'Dev Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);

        $this->command->info("Created temp admin: {$email}");
        $this->command->info("Password: {$password}");
    }
}
