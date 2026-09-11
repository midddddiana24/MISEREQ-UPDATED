<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\AdminActivityLog;
use App\Models\LoginAttempt;
use App\Models\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\UserSession;
use App\Models\TrustedAdminDevice;
use Tests\TestCase;

class AdminUserSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);
    }

    public function test_administrator_can_unlock_account_and_clear_login_throttle(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $user = $this->createUser([
            'failed_login_attempts' => 5,
            'locked_until' => now()->addMinutes(30),
        ]);
        $address = '192.0.2.25';
        LoginAttempt::create([
            'username' => $user->username,
            'ip_address' => $address,
            'success' => false,
            'failure_reason' => 'Invalid credentials',
        ]);

        $rateKey = 'login:' . strtolower($user->username) . '|' . $address;
        RateLimiter::hit($rateKey, 1800);
        $this->assertTrue(RateLimiter::tooManyAttempts($rateKey, 1));

        $this->actingAs($admin)
            ->post(route('admin.users.unlock', $user), [
                'unlock_reason' => 'User identity confirmed directly by the MIS Office.',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
        $this->assertFalse(RateLimiter::tooManyAttempts($rateKey, 1));
        $this->assertTrue(AdminActivityLog::where('action', 'user_unlocked')
            ->where('actor_id', $admin->id)
            ->exists());
        $this->assertTrue(Notification::where('user_id', $user->id)
            ->where('notification_type', 'security_alert')
            ->exists());
    }

    public function test_unlock_requires_a_reason(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $user = $this->createUser([
            'failed_login_attempts' => 5,
            'locked_until' => now()->addMinutes(30),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.unlock', $user), ['unlock_reason' => ''])
            ->assertSessionHasErrors('unlock_reason');
    }

    public function test_client_cannot_unlock_an_account(): void
    {
        $client = $this->createUser();
        $lockedUser = $this->createUser([
            'failed_login_attempts' => 5,
            'locked_until' => now()->addMinutes(30),
        ]);

        $this->actingAs($client)
            ->post(route('admin.users.unlock', $lockedUser), [
                'unlock_reason' => 'Unauthorized unlock attempt.',
            ])
            ->assertForbidden();
    }

    public function test_administrator_can_require_password_change(): void
    {
        [$admin, $user] = $this->adminAndUser();

        $this->actingAs($admin)
            ->post(route('admin.users.require-password-change', $user))
            ->assertRedirect();

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_administrator_can_revoke_sessions_and_trusted_devices(): void
    {
        [$admin, $user] = $this->adminAndUser();
        UserSession::create([
            'user_id' => $user->id,
            'session_id' => 'other-active-session',
            'last_activity_at' => now(),
        ]);
        TrustedAdminDevice::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', 'trusted-token'),
            'trusted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.force-logout', $user))
            ->assertRedirect();

        $this->assertNotNull(UserSession::first()->revoked_at);
        $this->assertNotNull(TrustedAdminDevice::first()->revoked_at);
    }

    public function test_administrator_can_send_secure_password_reset_invitation(): void
    {
        [$admin, $user] = $this->adminAndUser();

        $this->actingAs($admin)
            ->post(route('admin.users.send-password-reset', $user))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
        $this->assertTrue(AdminActivityLog::where('action', 'password_reset_invited')
            ->where('actor_id', $admin->id)
            ->exists());
    }

    public function test_required_password_change_blocks_access_until_completed(): void
    {
        $user = $this->createUser(['must_change_password' => true]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('password.required'));

        $this->put(route('password.required.update'), [
            'current_password' => 'StrongTestPassword!2026',
            'password' => 'NewStrongPassword!2027',
            'password_confirmation' => 'NewStrongPassword!2027',
        ])->assertRedirect(route('dashboard'));

        $this->assertFalse($user->fresh()->must_change_password);
    }

    private function adminAndUser(): array
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);
        $user = $this->createUser([
            'role' => 'user',
            'is_admin' => false,
            'is_active' => true,
        ]);

        return [$admin, $user];
    }
}
