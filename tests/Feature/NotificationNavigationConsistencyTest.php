<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class NotificationNavigationConsistencyTest extends TestCase
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

    public function test_user_feed_keeps_an_older_unread_alert_visible_ahead_of_recent_read_items(): void
    {
        $user = $this->createUser();
        $unread = $this->makeNotification($user, 'status_update', 'Older unread update');
        $unread->forceFill(['created_at' => now()->subDays(2)])->save();
        for ($index = 0; $index < 21; $index++) {
            $this->makeNotification($user, 'status_update', "Read update {$index}")
                ->forceFill(['read_at' => now()])->save();
        }

        $this->actingAs($user)->getJson(route('user.notifications'))
            ->assertOk()
            ->assertJsonPath('total_unread', 1)
            ->assertJsonPath('notifications.0.id', $unread->id)
            ->assertJsonPath('notifications.0.read', false);

        $this->actingAs($user)->get(route('user.notifications.index'))
            ->assertOk()
            ->assertSee('data-notification-open', false)
            ->assertSee(route('notification.read', $unread->id), false);

        $this->actingAs($user)->postJson(route('notification.read', $unread->id))->assertOk();
        $this->actingAs($user)->getJson(route('user.notifications'))
            ->assertJsonPath('total_unread', 0);
    }

    public function test_admin_feed_prioritizes_older_unread_alert_and_keeps_other_users_alerts_private(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $otherAdmin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $unread = $this->makeNotification($admin, 'admin_action_required', 'Older admin alert');
        $unread->forceFill(['created_at' => now()->subDays(2)])->save();
        for ($index = 0; $index < 11; $index++) {
            $this->makeNotification($admin, 'admin_action_required', "Read admin alert {$index}")
                ->forceFill(['read_at' => now()])->save();
        }
        $foreign = $this->makeNotification($otherAdmin, 'admin_action_required', 'Other admin alert');

        $this->actingAs($admin)->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total_unread', 1)
            ->assertJsonPath('notifications.0.id', 'action-'.$unread->id)
            ->assertDontSee('Other admin alert');
        $this->actingAs($admin)->postJson(route('admin.notifications.read', $foreign))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee(route('admin.notifications.read', $unread), false);

        $this->actingAs($admin)->postJson(route('admin.notifications.read', $unread))->assertOk();
        $this->actingAs($admin)->getJson(route('admin.notifications'))
            ->assertJsonPath('total_unread', 0);
    }

    public function test_notification_actions_clear_only_the_signed_in_accounts_alerts(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $otherAdmin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $userAlert = $this->makeNotification($user, 'status_update', 'User alert');
        $otherAlert = $this->makeNotification($other, 'status_update', 'Other user alert');
        $adminAlert = $this->makeNotification($admin, 'admin_action_required', 'Admin alert');
        $otherAdminAlert = $this->makeNotification($otherAdmin, 'admin_action_required', 'Other admin alert');

        $this->actingAs($user)->deleteJson(route('notifications.clear'))->assertOk();
        $this->assertDatabaseMissing('notifications', ['id' => $userAlert->id]);
        $this->assertDatabaseHas('notifications', ['id' => $otherAlert->id]);

        $this->actingAs($admin)->deleteJson(route('admin.notifications.delete', $otherAdminAlert))->assertForbidden();
        $this->actingAs($admin)->deleteJson(route('admin.notifications.clear'))->assertOk();
        $this->assertDatabaseMissing('notifications', ['id' => $adminAlert->id]);
        $this->assertDatabaseHas('notifications', ['id' => $otherAdminAlert->id]);
    }

    public function test_admin_page_actions_mark_and_delete_only_own_action_alerts(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $otherAdmin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $mine = $this->makeNotification($admin, 'admin_action_required', 'My action alert');
        $other = $this->makeNotification($otherAdmin, 'admin_action_required', 'Other action alert');
        $security = $this->makeNotification($admin, 'security_alert', 'Security notice');

        $this->actingAs($admin)->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee(route('admin.notifications.read-all'), false)
            ->assertSee(route('admin.notifications.clear'), false)
            ->assertSee(route('admin.notifications.delete', $mine), false)
            ->assertDontSee(route('admin.notifications.delete', $other), false)
            ->assertDontSee(route('admin.notifications.delete', $security), false);

        $this->actingAs($admin)->post(route('admin.notifications.read-all'))
            ->assertRedirect();
        $this->assertNotNull($mine->fresh()->read_at);
        $this->assertNull($other->fresh()->read_at);
        $this->assertNull($security->fresh()->read_at);

        $this->actingAs($admin)->delete(route('admin.notifications.delete', $mine))
            ->assertRedirect();
        $this->assertDatabaseMissing('notifications', ['id' => $mine->id]);
        $this->actingAs($admin)->deleteJson(route('admin.notifications.delete', $other))
            ->assertForbidden();
        $this->actingAs($admin)->deleteJson(route('admin.notifications.delete', $security))
            ->assertForbidden();
    }

    public function test_admin_bulk_clear_preserves_other_recipients_failed_delivery_records(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $other = $this->createUser();
        $ownAlert = $this->makeNotification($admin, 'admin_action_required', 'Own alert');
        $failed = $this->makeNotification($other, 'status_update', 'Failed email');
        $failed->forceFill(['status' => 'failed'])->save();

        $this->actingAs($admin)->delete(route('admin.notifications.clear'))
            ->assertRedirect();
        $this->assertDatabaseMissing('notifications', ['id' => $ownAlert->id]);
        $this->assertDatabaseHas('notifications', ['id' => $failed->id, 'status' => 'failed']);
    }

    public function test_user_notification_navigation_uses_the_application_base_path(): void
    {
        $user = $this->createUser();
        $root = 'http://localhost/mis-service-request/mis-service-request/public';
        URL::forceRootUrl($root);

        try {
            $this->actingAs($user);
            $html = view('user.partials.navigation')->render();
            $this->assertStringContainsString('data-notification-base-url="'.$root.'/api/notifications"', $html);
            $this->assertStringContainsString("fetch(notificationBaseUrl + '/' + notification.id + '/read'", $html);
        } finally {
            URL::forceRootUrl(config('app.url'));
        }
    }

    private function makeNotification($user, string $type, string $subject): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'recipient_email' => $user->email,
            'notification_type' => $type,
            'subject' => $subject,
            'message' => $subject,
            'action_url' => $type === 'admin_action_required' ? route('admin.requests.index') : route('requests.index'),
            'status' => 'sent',
        ]);
    }
}
