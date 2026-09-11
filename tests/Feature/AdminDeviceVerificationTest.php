<?php

namespace Tests\Feature;

use App\Models\AdminDeviceChallenge;
use App\Models\Notification;
use App\Models\TrustedAdminDevice;
use App\Services\AdminDeviceTrustService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminDeviceVerificationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_first_verified_admin_device_can_be_trusted(): void
    {
        $admin = $this->createUser([
            'username' => 'first.admin',
            'email' => 'first.admin@example.edu',
            'password' => 'StrongAdminPassword!2026',
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->post('/login', [
            'username' => $admin->username,
            'password' => 'StrongAdminPassword!2026',
        ])->assertRedirect(route('admin.device.verify'));

        $this->assertTrue((bool) session('admin_device_bootstrap'));

        $this->post(route('admin.device.continue'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseCount('trusted_admin_devices', 1);
        $this->assertFalse((bool) session('admin_device_bootstrap'));
    }

    public function test_new_admin_device_requires_challenge_and_accepts_emailed_code(): void
    {
        $admin = $this->createUser([
            'username' => 'secure.admin',
            'email' => 'secure.admin@example.edu',
            'password' => 'StrongAdminPassword!2026',
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);
        TrustedAdminDevice::create([
            'user_id' => $admin->id,
            'token_hash' => hash('sha256', 'different-device-token'),
            'device_name' => 'Existing trusted device',
            'trusted_at' => now(),
        ]);

        $this->withHeader('User-Agent', 'New Campus Browser')
            ->post('/login', [
                'username' => $admin->username,
                'password' => 'StrongAdminPassword!2026',
            ])
            ->assertRedirect(route('admin.device.verify'));

        $challenge = AdminDeviceChallenge::where('user_id', $admin->id)->firstOrFail();
        $this->assertStringNotContainsString(
            'Verification code:',
            Notification::where('user_id', $admin->id)->latest('id')->value('message')
        );
        $challenge->update(['code_hash' => Hash::make('123456')]);

        $this->assertAuthenticatedAs($admin);
        $this->post(route('admin.device.verify-code'), ['code' => '123456'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($challenge->fresh()->approved_at);
        $this->assertDatabaseCount('trusted_admin_devices', 2);
        $this->assertNull(session('admin_device_challenge_id'));
    }

    public function test_unapproved_device_cannot_open_admin_dashboard(): void
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);
        $challenge = AdminDeviceChallenge::create([
            'public_id' => fake()->uuid(),
            'user_id' => $admin->id,
            'session_id' => 'pending-session',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->actingAs($admin)
            ->withSession([
                '2fa_verified' => true,
                'admin_device_challenge_id' => $challenge->id,
            ])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.device.verify'));
    }

    public function test_trusted_admin_session_can_approve_another_device(): void
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);
        $token = 'trusted-approver-token';
        TrustedAdminDevice::create([
            'user_id' => $admin->id,
            'token_hash' => hash('sha256', $token),
            'device_name' => 'Trusted campus computer',
            'trusted_at' => now(),
        ]);
        $challenge = AdminDeviceChallenge::create([
            'public_id' => fake()->uuid(),
            'user_id' => $admin->id,
            'session_id' => 'new-device-session',
            'code_hash' => Hash::make('123456'),
            'device_name' => 'New mobile device',
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->actingAs($admin)
            ->withCookie(AdminDeviceTrustService::COOKIE_NAME, $token)
            ->get(route('admin.device.review', $challenge->public_id))
            ->assertOk();

        $this->post(route('admin.device.approve', $challenge->public_id))
            ->assertRedirect();

        $this->assertNotNull($challenge->fresh()->approved_at);
    }
}
