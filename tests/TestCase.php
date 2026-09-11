<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $app->detectEnvironment(fn () => 'testing');

        $liveDatabase = (string) env('PRODUCTION_DB_DATABASE', 'mis_service_request');
        $testDatabase = (string) config('database.connections.mysql.database');
        if ($testDatabase === $liveDatabase) {
            throw new \RuntimeException(
                'Unsafe test database configuration: automated tests cannot use the live MIS database.'
            );
        }

        $app['config']->set([
            'app.env' => 'testing',
            'cache.default' => 'array',
            'mail.default' => 'array',
            'queue.default' => 'sync',
            'session.driver' => 'array',
            'session.encrypt' => false,
            'session.secure' => false,
            'security.force_https' => false,
        ]);

        return $app;
    }

    protected function createUser(array $attributes = []): User
    {
        $suffix = Str::lower(Str::random(10));
        $user = User::create(array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'office' => 'MIS Test Office',
            'username' => "test_{$suffix}",
            'email' => "{$suffix}@example.test",
            'password' => 'StrongTestPassword!2026',
            'role' => 'user',
            'is_admin' => false,
            'is_active' => true,
            'password_expires_at' => now()->addYear(),
            'email_verified' => true,
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->saveQuietly();

        return $user->fresh();
    }
}
