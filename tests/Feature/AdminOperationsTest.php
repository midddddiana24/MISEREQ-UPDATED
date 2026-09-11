<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);
    }

    public function test_administrator_can_open_system_operations_and_audit_filters(): void
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
            'first_name' => 'MIS',
            'last_name' => 'Administrator',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.settings.operations'))
            ->assertOk()
            ->assertSee('System Operations')
            ->assertSee('Background processing')
            ->assertSee('Verified backups');

        $this->actingAs($admin)
            ->get(route('admin.operations', ['activity_module' => 'system']))
            ->assertOk()
            ->assertSee('Search activity')
            ->assertSee('Apply Filters');
    }

    public function test_client_cannot_open_administrator_operations(): void
    {
        $client = $this->createUser();

        $this->actingAs($client)
            ->get(route('admin.settings.operations'))
            ->assertForbidden();
    }

    public function test_sensitive_operation_requires_current_password(): void
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.settings.operations.backup'), [
                'current_password' => 'incorrect-password',
            ])
            ->assertSessionHasErrorsIn('backup', ['current_password']);
    }
}
