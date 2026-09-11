<?php

namespace App\Console\Commands;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Console\Command;

class ShowTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:show-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show available test users and service requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Available Test Users ===');

        $users = User::where('is_admin', false)
            ->select('id', 'first_name', 'last_name', 'email')
            ->limit(5)
            ->get();

        if ($users->isEmpty()) {
            $this->error('No non-admin users found!');

            return 1;
        }

        foreach ($users as $user) {
            $this->line("ID: {$user->id}, Name: {$user->first_name} {$user->last_name}, Email: {$user->email}");
        }

        $this->info("\n=== Available Service Requests ===");

        $requests = ServiceRequest::with('user')
            ->select('id', 'user_id', 'control_number', 'status')
            ->limit(10)
            ->get();

        if ($requests->isEmpty()) {
            $this->error('No service requests found!');

            return 1;
        }

        foreach ($requests as $req) {
            $userName = $req->user ? $req->user->first_name : 'Unknown';
            $this->line("ID: {$req->id}, User: {$req->user_id} ($userName), Control: {$req->control_number}, Status: {$req->status}");
        }

        $this->info("\n✅ Run the test command:");
        $user = $users->first();
        $req = $requests->first();
        $this->line("php artisan email:test-user-status-notification {$user->id} {$req->id} processing");

        return 0;
    }
}
