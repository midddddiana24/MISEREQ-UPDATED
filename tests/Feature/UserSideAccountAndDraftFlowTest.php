<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserSideAccountAndDraftFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        Storage::fake('secure');
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);
    }

    #[DataProvider('protectedRouteProvider')]
    public function test_logged_out_user_routes_redirect_to_login(string $method, string $uri): void
    {
        $response = $this->call($method, $uri);

        $response->assertRedirect(route('login'));
        $response->assertDontSee('Service Requests History');
    }

    public static function protectedRouteProvider(): array
    {
        return [
            ['GET', '/dashboard'],
            ['GET', '/requests'],
            ['GET', '/requests/create'],
            ['POST', '/requests'],
            ['GET', '/incident-reports'],
            ['GET', '/incident-reports/create'],
            ['POST', '/incident-reports'],
            ['GET', '/pm-checklists'],
            ['GET', '/user/messenger'],
            ['GET', '/user/notifications'],
            ['GET', '/user/profile'],
            ['GET', '/user/settings'],
            ['GET', '/user/security'],
            ['GET', '/api/user/notifications'],
            ['GET', '/api/user/login-history'],
            ['GET', '/api/request-draft'],
            ['PUT', '/api/request-draft'],
            ['DELETE', '/api/request-draft'],
            ['POST', '/api/messages/send'],
            ['POST', '/api/notifications/read-all'],
            ['DELETE', '/api/notifications'],
        ];
    }

    public function test_profile_validation_and_mass_assignment_protection_preserve_account_role(): void
    {
        $user = $this->createUser([
            'first_name' => 'Original',
            'last_name' => 'Requester',
            'office' => 'Registrar',
        ]);

        $this->actingAs($user)
            ->postJson(route('settings.updateProfile'), [
                'first_name' => '',
                'last_name' => str_repeat('L', 256),
                'office' => '',
                'current_password' => 'wrong-password',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'office', 'current_password']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Original',
            'role' => 'user',
            'is_admin' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('settings.updateProfile'), [
                'first_name' => 'Updated',
                'last_name' => 'Requester',
                'office' => 'Library',
                'current_password' => 'StrongTestPassword!2026',
                'role' => 'admin',
                'is_admin' => 1,
                'user_id' => 999999,
            ])
            ->assertRedirect(route('user.profile'));

        $user->refresh();
        $this->assertSame('Updated', $user->first_name);
        $this->assertSame('Library', $user->office);
        $this->assertSame('user', $user->role);
        $this->assertFalse((bool) $user->is_admin);
    }

    public function test_notification_preferences_persist_only_for_the_authenticated_user(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();

        $this->actingAs($user)
            ->post(route('settings.updateNotifications'), [
                'notify_email_enabled' => '1',
                'notify_request_updates' => '1',
                'notify_security_alerts' => '1',
            ])
            ->assertRedirect(route('user.settings'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'notify_email_enabled' => 1,
            'notify_request_updates' => 1,
            'notify_request_reminders' => 0,
            'notify_security_alerts' => 1,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $other->id,
            'notify_email_enabled' => (int) $other->notify_email_enabled,
        ]);
    }

    public function test_request_draft_full_lifecycle_and_invalid_payload(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->putJson(route('request-draft.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('payload');
        $this->assertDatabaseMissing('service_request_drafts', ['user_id' => $user->id]);

        $payload = [
            'services' => ['troubleshoot_pc'],
            'description' => 'Draft saved from the requester form.',
        ];
        $this->actingAs($user)
            ->putJson(route('request-draft.store'), ['payload' => $payload])
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertDatabaseHas('service_request_drafts', [
            'user_id' => $user->id,
            'form_type' => 'service_request',
        ]);

        $this->actingAs($user)
            ->getJson(route('request-draft.show'))
            ->assertOk()
            ->assertJsonPath('draft.payload.description', $payload['description']);

        $this->actingAs($user)
            ->deleteJson(route('request-draft.destroy'))
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertDatabaseMissing('service_request_drafts', ['user_id' => $user->id]);
    }

    public function test_empty_message_is_rejected_without_database_change(): void
    {
        $user = $this->createUser();
        $before = \App\Models\Message::count();

        $this->actingAs($user)
            ->postJson('/api/messages/send', [
                'service_request_id' => 999999,
                'message' => '',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['service_request_id', 'message']);

        $this->assertSame($before, \App\Models\Message::count());
    }
}
