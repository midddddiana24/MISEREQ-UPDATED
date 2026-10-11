<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Mail\SecurityAlertMail;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAService;
use Tests\TestCase;

class UserCredentialSecurityRoutesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutMiddleware([
            CheckSessionValidity::class,
        ]);
    }

    #[DataProvider('passwordRouteProvider')]
    public function test_password_routes_change_only_the_authenticated_user_and_revoke_other_sessions(
        string $method,
        string $uri
    ): void {
        $user = $this->createUser([
            'notify_email_enabled' => false,
            'notify_security_alerts' => false,
        ]);
        $other = $this->createUser();
        Setting::set('security_login_alerts', false, 'boolean', 'notifications');
        $otherDevice = $this->sessionRecord($user, 'other-device-'.uniqid());
        $otherUsersDevice = $this->sessionRecord($other, 'other-user-'.uniqid());

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->call($method, $uri, [
                'current_password' => 'StrongTestPassword!2026',
                'password' => 'UpdatedSecurePassword!2027',
                'password_confirmation' => 'UpdatedSecurePassword!2027',
                'user_id' => $other->id,
            ])->assertRedirect();

        $this->assertTrue(Hash::check('UpdatedSecurePassword!2027', $user->fresh()->password));
        $this->assertFalse(Hash::check('UpdatedSecurePassword!2027', $other->fresh()->password));
        $this->assertNotNull($otherDevice->fresh()->revoked_at);
        $this->assertNull($otherUsersDevice->fresh()->revoked_at);
        $this->assertSame(1, UserSession::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->count());
        $this->assertDatabaseHas('password_history', ['user_id' => $user->id]);
        Mail::assertSent(SecurityAlertMail::class, fn (SecurityAlertMail $mail) => $mail->hasTo($user->email)
            && $mail->notification->subject === 'Your MIS password was changed'
        );
    }

    #[DataProvider('passwordRouteProvider')]
    public function test_password_routes_reject_the_wrong_current_password(string $method, string $uri): void
    {
        $user = $this->createUser();
        $otherDevice = $this->sessionRecord($user, 'wrong-password-device-'.uniqid());

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->call($method, $uri, [
                'current_password' => 'WrongPassword!2026',
                'password' => 'UpdatedSecurePassword!2027',
                'password_confirmation' => 'UpdatedSecurePassword!2027',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('StrongTestPassword!2026', $user->fresh()->password));
        $this->assertNull($otherDevice->fresh()->revoked_at);
    }

    #[DataProvider('passwordRouteProvider')]
    public function test_password_routes_require_completed_two_factor_verification(string $method, string $uri): void
    {
        [$user] = $this->userWithTwoFactor();

        $this->actingAs($user)
            ->call($method, $uri, [
                'current_password' => 'StrongTestPassword!2026',
                'password' => 'UpdatedSecurePassword!2027',
                'password_confirmation' => 'UpdatedSecurePassword!2027',
            ])
            ->assertRedirect(route('2fa.verify'));

        $this->assertTrue(Hash::check('StrongTestPassword!2026', $user->fresh()->password));
    }

    public static function passwordRouteProvider(): array
    {
        return [
            'settings password' => ['POST', '/user/settings/password'],
            'security page password alias' => ['POST', '/user/password'],
            'framework password update' => ['PUT', '/password'],
        ];
    }

    public function test_settings_two_factor_setup_requires_password_then_a_valid_authenticator_code(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();

        $setup = $this->actingAs($user)
            ->postJson(route('settings.2fa.setup'), [
                'current_password' => 'StrongTestPassword!2026',
                'user_id' => $other->id,
            ])
            ->assertOk()
            ->assertJsonStructure(['secret', 'qr_code']);

        $secret = $setup->json('secret');
        $this->post(route('settings.2fa.enable'), [
            'verification_code' => (new Google2FAService)->getCurrentOtp($secret),
            'user_id' => $other->id,
        ])->assertRedirect(route('settings.2fa.recovery-codes'));

        $user->refresh();
        $this->assertTrue($user->isTwoFactorEnabled());
        $this->assertSame($secret, $user->google2fa_secret);
        $this->assertCount(8, $user->two_factor_recovery_code);
        $this->assertFalse($other->fresh()->isTwoFactorEnabled());
    }

    public function test_settings_two_factor_setup_rejects_wrong_password_and_wrong_code(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson(route('settings.2fa.setup'), ['current_password' => 'WrongPassword!2026'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');

        $setup = $this->postJson(route('settings.2fa.setup'), [
            'current_password' => 'StrongTestPassword!2026',
        ])->assertOk();
        $validCode = (new Google2FAService)->getCurrentOtp($setup->json('secret'));

        $this->post(route('settings.2fa.enable'), [
            'verification_code' => $this->differentOtp($validCode),
        ])->assertSessionHasErrors('verification_code');

        $this->assertFalse($user->fresh()->isTwoFactorEnabled());
    }

    public function test_two_factor_disable_requires_password_and_current_code_and_cannot_target_another_user(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();
        [$other] = $this->userWithTwoFactor();
        $user->forceFill([
            'notify_email_enabled' => false,
            'notify_security_alerts' => false,
        ])->save();
        Setting::set('security_login_alerts', false, 'boolean', 'notifications');

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->post(route('settings.2fa.disable'), [
                'current_password' => 'StrongTestPassword!2026',
                'verification_code' => (new Google2FAService)->getCurrentOtp($secret),
                'user_id' => $other->id,
            ])
            ->assertRedirect(route('user.settings'));

        $this->assertFalse($user->fresh()->isTwoFactorEnabled());
        $this->assertTrue($other->fresh()->isTwoFactorEnabled());
        Mail::assertSent(SecurityAlertMail::class, fn (SecurityAlertMail $mail) => $mail->hasTo($user->email)
            && $mail->notification->subject === 'Two-factor authentication was disabled'
        );
    }

    public function test_two_factor_disable_rejects_wrong_password_and_wrong_code(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();
        $validCode = (new Google2FAService)->getCurrentOtp($secret);

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->post(route('settings.2fa.disable'), [
                'current_password' => 'WrongPassword!2026',
                'verification_code' => $validCode,
            ])
            ->assertSessionHasErrors('current_password');

        $this->post(route('settings.2fa.disable'), [
            'current_password' => 'StrongTestPassword!2026',
            'verification_code' => $this->differentOtp($validCode),
        ])->assertSessionHasErrors('verification_code');

        $this->assertTrue($user->fresh()->isTwoFactorEnabled());
    }

    public function test_recovery_codes_require_recent_password_confirmation(): void
    {
        [$user] = $this->userWithTwoFactor();

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->get(route('settings.2fa.recovery-codes'))
            ->assertRedirect(route('password.confirm'));

        $this->withSession([
            '2fa_verified' => true,
            'auth.password_confirmed_at' => time(),
        ])->get(route('settings.2fa.recovery-codes'))
            ->assertOk();
    }

    public function test_recovery_code_regeneration_requires_password_and_code_and_is_user_scoped(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();
        [$other] = $this->userWithTwoFactor();
        $oldCodes = $user->two_factor_recovery_code;
        $otherCodes = $other->two_factor_recovery_code;

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->post(route('settings.2fa.recovery-codes.regenerate'), [
                'current_password' => 'StrongTestPassword!2026',
                'verification_code' => (new Google2FAService)->getCurrentOtp($secret),
                'user_id' => $other->id,
            ])
            ->assertRedirect(route('settings.2fa.recovery-codes'))
            ->assertSessionHas('new_recovery_codes');

        $this->assertNotSame($oldCodes, $user->fresh()->two_factor_recovery_code);
        $this->assertSame($otherCodes, $other->fresh()->two_factor_recovery_code);
    }

    public function test_recovery_code_regeneration_rejects_wrong_password_and_wrong_code(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();
        $oldCodes = $user->two_factor_recovery_code;
        $validCode = (new Google2FAService)->getCurrentOtp($secret);

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->post(route('settings.2fa.recovery-codes.regenerate'), [
                'current_password' => 'WrongPassword!2026',
                'verification_code' => $validCode,
            ])->assertSessionHasErrors('current_password');

        $this->post(route('settings.2fa.recovery-codes.regenerate'), [
            'current_password' => 'StrongTestPassword!2026',
            'verification_code' => $this->differentOtp($validCode),
        ])->assertSessionHasErrors('verification_code');

        $this->assertSame($oldCodes, $user->fresh()->two_factor_recovery_code);
    }

    public function test_user_can_revoke_an_owned_session_only_with_current_password(): void
    {
        $user = $this->createUser();
        $session = $this->sessionRecord($user, 'owned-session-'.uniqid());

        $this->actingAs($user)
            ->delete(route('settings.sessions.destroy', $session), [
                'current_password' => 'WrongPassword!2026',
            ])->assertSessionHasErrors('current_password');
        $this->assertNull($session->fresh()->revoked_at);

        $this->delete(route('settings.sessions.destroy', $session), [
            'current_password' => 'StrongTestPassword!2026',
        ])->assertSessionHas('success');
        $this->assertNotNull($session->fresh()->revoked_at);
    }

    public function test_user_cannot_revoke_another_users_session_by_id(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        $otherSession = $this->sessionRecord($other, 'other-owned-session-'.uniqid());

        $this->actingAs($user)
            ->delete(route('settings.sessions.destroy', $otherSession), [
                'current_password' => 'StrongTestPassword!2026',
            ])->assertForbidden();

        $this->assertNull($otherSession->fresh()->revoked_at);
    }

    public function test_logout_other_sessions_requires_password_and_does_not_touch_another_user(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        $first = $this->sessionRecord($user, 'first-device-'.uniqid());
        $second = $this->sessionRecord($user, 'second-device-'.uniqid());
        $otherSession = $this->sessionRecord($other, 'unrelated-device-'.uniqid());

        $this->actingAs($user)
            ->post(route('settings.sessions.logout-others'), [
                'current_password' => 'WrongPassword!2026',
            ])->assertSessionHasErrors('current_password');
        $this->assertNull($first->fresh()->revoked_at);
        $this->assertNull($second->fresh()->revoked_at);

        $this->post(route('settings.sessions.logout-others'), [
            'current_password' => 'StrongTestPassword!2026',
            'user_id' => $other->id,
        ])->assertSessionHas('success');

        $this->assertNotNull($first->fresh()->revoked_at);
        $this->assertNotNull($second->fresh()->revoked_at);
        $this->assertNull($otherSession->fresh()->revoked_at);
    }

    #[DataProvider('guestSecurityRouteProvider')]
    public function test_security_management_routes_block_guests(string $method, string $uri): void
    {
        if (str_contains($uri, '{session}')) {
            $owner = $this->createUser();
            $session = $this->sessionRecord($owner, 'guest-target-'.uniqid());
            $uri = str_replace('{session}', (string) $session->id, $uri);
        }

        $this->call($method, $uri)->assertRedirect(route('login'));
    }

    public static function guestSecurityRouteProvider(): array
    {
        return [
            ['POST', '/user/settings/password'],
            ['POST', '/user/password'],
            ['PUT', '/password'],
            ['POST', '/user/settings/2fa/setup'],
            ['POST', '/user/settings/2fa/enable'],
            ['POST', '/user/settings/2fa/disable'],
            ['GET', '/user/settings/2fa/recovery-codes'],
            ['POST', '/user/settings/2fa/recovery-codes'],
            ['DELETE', '/user/settings/sessions/{session}'],
            ['POST', '/user/settings/sessions/logout-others'],
        ];
    }

    private function userWithTwoFactor(): array
    {
        $secret = (new Google2FAService)->generateSecretKey();
        $user = $this->createUser();
        $user->forceFill([
            'google2fa_secret' => $secret,
            'two_fa_enabled_at' => now(),
            'two_factor_recovery_code' => [Hash::make('TEST-RECOVERY')],
        ])->saveQuietly();

        return [$user->fresh(), $secret];
    }

    private function sessionRecord(User $user, string $sessionId): UserSession
    {
        return UserSession::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'device_name' => 'Test device',
            'last_activity_at' => now(),
        ]);
    }

    private function differentOtp(string $validCode): string
    {
        $last = (int) substr($validCode, -1);

        return substr($validCode, 0, -1).(($last + 1) % 10);
    }
}
