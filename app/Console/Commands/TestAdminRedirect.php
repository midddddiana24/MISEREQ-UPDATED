<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestAdminRedirect extends Command
{
    protected $signature = 'test:admin-redirect {username}';
    protected $description = 'Test admin redirect logic';

    public function handle()
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User '$username' not found");
            return;
        }

        $this->info("User: $username");
        $this->info("is_admin: " . ($user->is_admin ? 'true' : 'false'));
        $this->info("role: " . $user->role);
        $this->info("isAdmin() method: " . ($user->isAdmin() ? 'true' : 'false'));

        if ($user->isAdmin()) {
            $this->info("✅ Should redirect to: /admin/dashboard");
        } else {
            $this->info("✅ Should redirect to: /dashboard");
        }
    }
}
