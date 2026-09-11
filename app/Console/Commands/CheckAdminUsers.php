<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckAdminUsers extends Command
{
    protected $signature = 'check:admin-users';

    protected $description = 'Check admin users in the database';

    public function handle()
    {
        $this->info('Checking admin users...');

        $admins = User::where('is_admin', true)
            ->orWhere('role', 'admin')
            ->get(['id', 'username', 'email', 'is_admin', 'role']);

        if ($admins->isEmpty()) {
            $this->warn('No admin users found!');

            return;
        }

        $this->table(
            ['ID', 'Username', 'Email', 'is_admin', 'role'],
            $admins->map(fn ($u) => [$u->id, $u->username, $u->email, $u->is_admin ? 'true' : 'false', $u->role])
        );
    }
}
