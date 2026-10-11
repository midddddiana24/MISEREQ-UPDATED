<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoginLockPolicyTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_configured_client_threshold_and_duration_are_enforced_by_login(): void
    {
        Setting::set('client_login_lock_attempts', 10, 'integer', 'security');
        Setting::set('client_login_lock_minutes', 15, 'integer', 'security');
        $client = $this->createUser();

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->post(route('login'), [
                'username' => $client->username,
                'password' => 'WrongPassword!2026',
            ])->assertSessionHasErrors('username');
        }

        $client->refresh();
        $this->assertSame(10, $client->failed_login_attempts);
        $this->assertTrue($client->isAccountLocked());
        $this->assertTrue($client->locked_until->between(now()->addMinutes(14), now()->addMinutes(16)));

        // The route also caps a single IP at ten submissions per minute.
        $this->travel(61)->seconds();
        $lockedResponse = $this->post(route('login'), [
            'username' => $client->username,
            'password' => 'StrongTestPassword!2026',
        ]);
        $lockedResponse->assertSessionHasErrors('username');
        $this->assertStringContainsString('Try again in about 14 minutes', session('errors')->first('username'));
        $this->assertGuest();
    }

    public function test_failures_outside_observation_window_do_not_accumulate(): void
    {
        $client = $this->createUser([
            'failed_login_attempts' => 4,
            'failed_login_window_started_at' => now()->subMinutes(31),
        ]);

        $this->post(route('login'), [
            'username' => $client->username,
            'password' => 'WrongPassword!2026',
        ])->assertSessionHasErrors('username');

        $client->refresh();
        $this->assertSame(1, $client->failed_login_attempts);
        $this->assertFalse($client->isAccountLocked());
        $this->assertTrue($client->failed_login_window_started_at->gt(now()->subMinute()));
    }

    public function test_first_wrong_password_after_lock_expires_starts_a_fresh_count(): void
    {
        Setting::set('client_login_lock_minutes', 15, 'integer', 'security');
        $client = $this->createUser([
            'failed_login_attempts' => 5,
            'failed_login_window_started_at' => now()->subMinutes(16),
            'locked_until' => now()->subMinute(),
        ]);

        $this->post(route('login'), [
            'username' => $client->username,
            'password' => 'WrongPassword!2026',
        ])->assertSessionHasErrors('username');

        $client->refresh();
        $this->assertSame(1, $client->failed_login_attempts);
        $this->assertNull($client->locked_until);
    }

    public function test_admin_limit_stays_fixed_when_client_policy_changes(): void
    {
        Setting::set('client_login_lock_attempts', 10, 'integer', 'security');
        Setting::set('client_login_lock_minutes', 15, 'integer', 'security');
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login'), [
                'username' => $admin->username,
                'password' => 'WrongPassword!2026',
            ])->assertSessionHasErrors('username');
        }

        $admin->refresh();
        $this->assertTrue($admin->isAccountLocked());
        $this->assertSame(5, $admin->failed_login_attempts);
        $this->assertTrue($admin->locked_until->between(now()->addMinutes(29), now()->addMinutes(31)));
    }

    public function test_client_settings_show_read_only_lock_policy_and_clear_security_email_copy(): void
    {
        Setting::set('client_login_lock_attempts', 10, 'integer', 'security');
        Setting::set('client_login_lock_minutes', 60, 'integer', 'security');
        $client = $this->createUser();

        $this->actingAs($client)->get(route('user.settings'))
            ->assertOk()
            ->assertSee('10 incorrect passwords within 30 minutes temporarily lock your account for 60 minutes')
            ->assertSee('Optional security emails')
            ->assertDontSee('name="client_login_lock_attempts"', false);
    }

    public function test_browser_login_throttle_returns_to_form_with_retry_time(): void
    {
        $client = $this->createUser();
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->post(route('login'), [
                'username' => $client->username,
                'password' => 'WrongPassword!2026',
            ]);
        }

        $this->from(route('login'))->post(route('login'), [
            'username' => $client->username,
            'password' => 'WrongPassword!2026',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('username');
        $this->assertStringContainsString('Too many sign-in attempts', session('errors')->first('username'));
    }
}
