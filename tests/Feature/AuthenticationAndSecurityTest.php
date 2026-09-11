<?php

namespace Tests\Feature;

use App\Exceptions\Handler;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AuthenticationAndSecurityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_account_pages_are_available_with_security_headers(): void
    {
        foreach (['/', '/login', '/register', '/forgot-password'] as $path) {
            $response = $this->get($path);
            $response->assertOk()
                ->assertHeader('X-Frame-Options', 'DENY')
                ->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->assertNotEmpty($response->headers->get('Content-Security-Policy'));
        }
    }

    public function test_client_can_sign_in_and_remember_option_is_accepted(): void
    {
        Mail::fake();
        $user = $this->createUser();

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'StrongTestPassword!2026',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_administrator_with_two_factor_continues_to_admin_device_verification(): void
    {
        Mail::fake();
        $google2fa = new Google2FAService();
        $secret = $google2fa->generateSecretKey();
        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
            'google2fa_secret' => $secret,
            'two_fa_enabled_at' => now(),
        ]);

        $this->withSession(['url.intended' => route('dashboard')])
            ->post('/login', [
                'username' => $admin->username,
                'password' => 'StrongTestPassword!2026',
            ])
            ->assertRedirect(route('2fa.verify'));

        $this->post('/2fa/verify', [
            'code' => $google2fa->getCurrentOtp($secret),
        ])
            ->assertRedirect(route('admin.device.verify'))
            ->assertSessionHas('2fa_verified', true);

        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('url.intended'));
    }

    public function test_authenticated_administrator_cannot_be_redirected_to_client_dashboard_from_login(): void
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/login')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_guest_cannot_open_admin_operations(): void
    {
        $this->get('/admin/settings/operations')
            ->assertRedirect(route('login'));
    }

    public function test_http_requests_redirect_to_configured_https_origin_when_enabled(): void
    {
        config([
            'security.force_https' => true,
            'app.url' => 'https://campus.example.test',
        ]);

        $this->get('http://localhost/login')
            ->assertStatus(301)
            ->assertRedirect('https://campus.example.test/login');
    }

    public function test_json_rate_limit_response_is_clear_and_retryable(): void
    {
        for ($attempt = 1; $attempt <= 11; $attempt++) {
            $response = $this->postJson('/login', [
                'username' => "missing{$attempt}@example.test",
                'password' => 'WrongPassword!2026',
            ]);
        }

        $response->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['message', 'error', 'retry_after']);
    }

    public function test_expired_login_form_recovers_instead_of_showing_419_page(): void
    {
        $this->assertSame('mis_service_request_session_v2', config('session.cookie'));

        $request = Request::create('/login', 'POST');
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        $response = app(Handler::class)->render(
            $request,
            new HttpException(419, 'CSRF token mismatch.')
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->getTargetUrl());
        $this->assertSame(
            'Your sign-in page expired and was refreshed. Please enter your password again.',
            $session->get('warning')
        );
    }
}
