<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\UserSession;
use App\Jobs\NotifyAdminNewRequest;
use App\Jobs\SendRequestCreatedNotification;
use App\Models\Notification;
use App\Models\AdminActivityLog;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Services\AdminActionNotificationService;
use App\Services\EmailNotificationService;
use App\Services\SecurityNotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminSettingsIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Mail::mailer()->getSymfonyTransport()->flush();
        $this->withoutMiddleware(CheckSessionValidity::class);
    }

    public function test_admin_can_save_every_control_and_switch_it_back_off(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $this->withoutMiddleware(CheckTwoFactorAuth::class);
        $on = $this->validSettings();

        $this->actingAs($admin)->patch(route('admin.settings.update'), $on)
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasNoErrors();

        foreach (['notify_new_requests', 'notify_request_updates', 'notify_completed_requests', 'notify_admin_alerts', 'security_login_alerts', 'require_2fa_admins', 'require_2fa_clients'] as $key) {
            $this->assertTrue(Setting::get($key), "{$key} was not enabled.");
        }
        $this->assertSame('mis-help@example.test', Setting::get('support_email'));
        $this->assertSame(15, Setting::get('max_upload_size'));
        $this->assertSame(30, Setting::get('client_session_timeout'));
        $this->assertSame(2, Setting::get('max_active_client_sessions'));
        $this->assertSame(10, Setting::get('client_login_lock_attempts'));
        $this->assertSame(15, Setting::get('client_login_lock_minutes'));
        $this->assertTrue(AdminActivityLog::where('actor_id', $admin->id)->where('action', 'client_login_lock_policy_changed')->exists());

        $off = $on;
        foreach (['notify_new_requests', 'notify_request_updates', 'notify_completed_requests', 'notify_admin_alerts', 'security_login_alerts', 'require_2fa_admins', 'require_2fa_clients'] as $key) {
            $off[$key] = '0';
        }
        $this->actingAs($admin)->patch(route('admin.settings.update'), $off)->assertRedirect();
        foreach (['notify_new_requests', 'notify_request_updates', 'notify_completed_requests', 'notify_admin_alerts', 'security_login_alerts', 'require_2fa_admins', 'require_2fa_clients'] as $key) {
            $this->assertFalse(Setting::get($key), "{$key} was not disabled.");
        }
    }

    public function test_only_administrators_can_change_settings_and_invalid_values_are_rejected(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $client = $this->createUser();
        $this->get(route('admin.settings'))->assertRedirect(route('login'));
        $this->actingAs($client)->patch(route('admin.settings.update'), $this->validSettings())->assertForbidden();

        config()->set('session.lifetime', 120);
        $this->actingAs($admin)->get(route('admin.settings'))
            ->assertOk()
            ->assertDontSee('<option value="240"', false)
            ->assertSee('Limited by the server session lifetime');

        $invalid = array_merge($this->validSettings(), [
            'max_upload_size' => 21,
            'client_session_timeout' => 240,
            'client_login_lock_attempts' => 0,
            'client_login_lock_minutes' => 120,
        ]);
        $this->actingAs($admin)->patch(route('admin.settings.update'), $invalid)
            ->assertSessionHasErrors(['max_upload_size', 'client_session_timeout', 'client_login_lock_attempts', 'client_login_lock_minutes']);
        $this->assertNotSame(21, Setting::get('max_upload_size'));
    }

    public function test_test_email_uses_saved_support_address_and_reports_array_transport_honestly(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        Setting::set('support_email', 'mis-help@example.test', 'string', 'system');

        $this->actingAs($admin)->post(route('admin.settings.test-email'))
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('success', 'Test email processed by the array mailer. No external email was delivered.');

        $sent = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);
        $this->assertSame('mis-help@example.test', $sent->first()->getEnvelope()->getRecipients()[0]->getAddress());
    }

    public function test_notification_switches_affect_client_mail_and_admin_alerts_but_keep_client_feed(): void
    {
        $client = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $request = $this->makeRequest($client);
        foreach (['notify_new_requests', 'notify_request_updates', 'notify_completed_requests', 'notify_admin_alerts'] as $key) {
            Setting::set($key, false, 'boolean', 'notifications');
        }

        (new SendRequestCreatedNotification($request))->handle();
        app(EmailNotificationService::class)->sendStatusUpdateNotification($request, 'pending', 'processing');
        app(EmailNotificationService::class)->sendRequestCompletedNotification($request);
        app(AdminActionNotificationService::class)->notify('Action required', 'Check the request', route('admin.requests.index'), $request->id);
        (new NotifyAdminNewRequest($request, $client))->handle();

        $this->assertCount(0, Mail::mailer()->getSymfonyTransport()->messages());
        $this->assertSame(3, Notification::where('user_id', $client->id)->count());
        $this->assertFalse(Notification::where('user_id', $admin->id)->where('notification_type', 'admin_action_required')->exists());

        foreach (['notify_new_requests', 'notify_request_updates', 'notify_completed_requests', 'notify_admin_alerts'] as $key) {
            Setting::set($key, true, 'boolean', 'notifications');
        }
        (new SendRequestCreatedNotification($request))->handle();
        $this->assertCount(1, Mail::mailer()->getSymfonyTransport()->messages(), 'Request confirmation email was not sent.');
        app(EmailNotificationService::class)->sendStatusUpdateNotification($request, 'pending', 'processing');
        $this->assertCount(2, Mail::mailer()->getSymfonyTransport()->messages(), 'Status update email was not sent.');
        app(EmailNotificationService::class)->sendRequestCompletedNotification($request);
        $this->assertCount(3, Mail::mailer()->getSymfonyTransport()->messages(), 'Completion email was not sent: '.Notification::where('user_id', $client->id)->where('notification_type', 'request_completed')->latest('id')->value('response'));
        $completionHtml = Mail::mailer()->getSymfonyTransport()->messages()->last()->getOriginalMessage()->getHtmlBody();
        $this->assertStringContainsString(route('requests.show', $request), $completionHtml);
        $this->assertStringNotContainsString('/dashboard/requests/'.$request->id.'/rate', $completionHtml);
        app(AdminActionNotificationService::class)->notify('Action required', 'Check the request', route('admin.requests.index'), $request->id);
        (new NotifyAdminNewRequest($request, $client))->handle();

        $this->assertCount(4, Mail::mailer()->getSymfonyTransport()->messages());
        $this->assertTrue(Notification::where('user_id', $admin->id)->where('notification_type', 'admin_action_required')->exists());
    }

    public function test_optional_security_email_toggle_does_not_suppress_mandatory_email(): void
    {
        $client = $this->createUser();
        Setting::set('security_login_alerts', false, 'boolean', 'notifications');

        app(SecurityNotificationService::class)->notify($client, 'Signed-out device', 'An older session was removed.');
        $this->assertCount(0, Mail::mailer()->getSymfonyTransport()->messages());

        app(SecurityNotificationService::class)->notifyMandatory(
            SecurityNotificationService::EVENT_PASSWORD_CHANGED,
            $client,
            'Password changed',
            'Your password changed.'
        );
        $this->assertCount(1, Mail::mailer()->getSymfonyTransport()->messages());
        $this->assertSame(2, Notification::where('user_id', $client->id)->where('notification_type', 'security_alert')->count());
    }

    public function test_admin_2fa_requirement_is_enforced_by_the_existing_middleware(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $client = $this->createUser();
        Setting::set('require_2fa_admins', true, 'boolean', 'security');
        Setting::set('require_2fa_clients', true, 'boolean', 'security');

        $this->actingAs($admin)->get(route('admin.settings'))->assertRedirect(route('2fa.setup'));
        $this->actingAs($client)->get(route('dashboard'))->assertRedirect(route('2fa.setup'));
    }

    public function test_unverified_2fa_session_cannot_use_user_api_routes(): void
    {
        $client = $this->createUser(['two_fa_enabled_at' => now()]);

        $this->actingAs($client)->withSession(['2fa_verified' => false])
            ->getJson(route('user.notifications'))
            ->assertForbidden()
            ->assertJsonPath('message', 'Two-factor verification is required.');

        $this->actingAs($client)->withSession(['2fa_verified' => false])
            ->get(route('user.notifications'))
            ->assertRedirect(route('2fa.verify'));
    }

    public function test_upload_limit_changes_user_request_validation(): void
    {
        $client = $this->createUser();
        Storage::fake('secure');
        Setting::set('max_upload_size', 5, 'integer', 'system');

        $payload = [
            'services' => ['troubleshoot_pc'],
            'description' => 'Test the administrator file size setting.',
            'date_request' => now()->toDateString(),
            'time_request' => now()->format('H:i'),
        ];

        $this->actingAs($client)->get(route('requests.create'))->assertOk()->assertSee('maximum 5MB each');
        $this->actingAs($client)->from(route('requests.create'))
            ->post(route('requests.store'), $payload + [
                'attachment' => UploadedFile::fake()->create('evidence.pdf', 6 * 1024, 'application/pdf'),
            ])->assertSessionHasErrors('attachment');

        Setting::set('max_upload_size', 10, 'integer', 'system');
        $this->actingAs($client)->get(route('requests.create'))->assertOk()->assertSee('maximum 10MB each');
        $this->actingAs($client)->from(route('requests.create'))
            ->post(route('requests.store'), $payload + [
                'attachment' => UploadedFile::fake()->create('evidence.pdf', 6 * 1024, 'application/pdf'),
            ])->assertSessionHasNoErrors();

        $this->assertSame(1, ServiceRequest::where('user_id', $client->id)->count());
    }

    public function test_client_idle_timeout_never_exceeds_server_session_lifetime(): void
    {
        $client = $this->createUser();
        config()->set('session.lifetime', 120);
        Setting::set('client_session_timeout', 240, 'integer', 'security');
        $this->actingAs($client);
        $httpRequest = Request::create(route('dashboard'));
        $httpRequest->setLaravelSession(app('session')->driver());
        $httpRequest->setUserResolver(fn () => $client);
        $session = UserSession::create([
            'user_id' => $client->id,
            'session_id' => $httpRequest->session()->getId(),
            'last_activity_at' => now()->subMinutes(130),
        ]);

        $response = app(TrackUserSession::class)->handle($httpRequest, fn () => response('Allowed'));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->getTargetUrl());
        $this->assertNotNull($session->fresh()->revoked_at);
    }

    public function test_client_device_limit_revokes_older_sessions_when_a_new_one_starts(): void
    {
        $client = $this->createUser();
        Setting::set('max_active_client_sessions', 1, 'integer', 'security');
        $older = UserSession::create([
            'user_id' => $client->id,
            'session_id' => 'settings-integration-older-session',
            'last_activity_at' => now()->subMinute(),
        ]);

        $this->actingAs($client)->get(route('dashboard'))->assertOk();

        $this->assertNotNull($older->fresh()->revoked_at);
        $this->assertSame(1, UserSession::where('user_id', $client->id)->whereNull('revoked_at')->count());
    }

    public function test_saved_client_lock_policy_controls_real_login_attempts(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $client = $this->createUser();
        $this->withoutMiddleware(CheckTwoFactorAuth::class);

        $this->actingAs($admin)->patch(route('admin.settings.update'), $this->validSettings())
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasNoErrors();
        $this->actingAs($admin)->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Client sign-in protection')
            ->assertSee('10 failed attempts');

        auth()->logout();
        for ($attempt = 1; $attempt <= 9; $attempt++) {
            $this->post(route('login'), [
                'username' => $client->username,
                'password' => 'WrongPassword!2026',
            ])->assertSessionHasErrors('username');
        }
        $this->assertFalse($client->fresh()->isAccountLocked());

        $this->post(route('login'), [
            'username' => $client->username,
            'password' => 'WrongPassword!2026',
        ])->assertSessionHasErrors('username');
        $this->assertTrue($client->fresh()->isAccountLocked());
        $this->assertSame(10, $client->fresh()->failed_login_attempts);
        $this->assertTrue($client->fresh()->locked_until->between(now()->addMinutes(14), now()->addMinutes(16)));
    }

    private function validSettings(): array
    {
        return [
            'support_email' => 'mis-help@example.test',
            'max_upload_size' => 15,
            'client_session_timeout' => 30,
            'max_active_client_sessions' => 2,
            'client_login_lock_attempts' => 10,
            'client_login_lock_minutes' => 15,
            'notify_new_requests' => '1',
            'notify_request_updates' => '1',
            'notify_completed_requests' => '1',
            'notify_admin_alerts' => '1',
            'security_login_alerts' => '1',
            'require_2fa_admins' => '1',
            'require_2fa_clients' => '1',
        ];
    }

    private function makeRequest($owner): ServiceRequest
    {
        return ServiceRequest::create([
            'control_number' => 'TEST-SETTINGS-'.Str::upper(Str::random(8)),
            'user_id' => $owner->id,
            'requester_name' => $owner->full_name,
            'requester_office' => $owner->office,
            'office' => $owner->office,
            'services' => ['troubleshoot_pc'],
            'description' => 'Settings integration test request.',
            'status' => 'pending',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
        ]);
    }
}
