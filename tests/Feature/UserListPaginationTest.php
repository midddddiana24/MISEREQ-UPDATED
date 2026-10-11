<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\LoginAttempt;
use App\Models\Notification;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserListPaginationTest extends TestCase
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

    public function test_request_search_finds_older_records_and_preserves_the_status_filter(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        for ($index = 0; $index < 7; $index++) {
            $this->makeRequest($user, "PAGING-{$index}", $index === 0 ? 'needle older request' : 'Routine help');
        }
        $this->makeRequest($other, 'FOREIGN-REQUEST', 'needle foreign request');

        $this->actingAs($user)->get(route('requests.index'))
            ->assertOk()
            ->assertSee('Page 1 of 2')
            ->assertDontSee('PAGING-0')
            ->assertDontSee('FOREIGN-REQUEST');

        $this->actingAs($user)->get(route('requests.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('PAGING-0');

        $this->actingAs($user)->get(route('requests.index', ['status' => 'active', 'search' => 'needle']))
            ->assertOk()
            ->assertSee('PAGING-0')
            ->assertDontSee('FOREIGN-REQUEST')
            ->assertSee('value="needle"', false)
            ->assertSee('name="status" value="active"', false);
    }

    public function test_notification_page_shows_unread_first_then_paginates(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        for ($index = 0; $index < 7; $index++) {
            Notification::create([
                'user_id' => $user->id,
                'recipient_email' => $user->email,
                'notification_type' => 'status_update',
                'subject' => "Read item {$index}",
                'message' => "Read item {$index}",
                'status' => 'sent',
                'read_at' => now(),
            ]);
        }
        Notification::create([
            'user_id' => $user->id,
            'recipient_email' => $user->email,
            'notification_type' => 'status_update',
            'subject' => 'Important unread item',
            'message' => 'Important unread item',
            'status' => 'sent',
            'read_at' => null,
        ])->forceFill(['created_at' => now()->subDays(2)])->save();
        Notification::create([
            'user_id' => $other->id,
            'recipient_email' => $other->email,
            'notification_type' => 'status_update',
            'subject' => 'Private item',
            'message' => 'Private item',
            'status' => 'sent',
        ]);

        $this->actingAs($user)->get(route('user.notifications.index'))
            ->assertOk()
            ->assertSee('Important unread item')
            ->assertSee('Page 1 of 2')
            ->assertDontSee('Private item');
    }

    public function test_login_history_details_and_older_pages_remain_available(): void
    {
        $user = $this->createUser();
        for ($index = 0; $index < 7; $index++) {
            $attempt = new LoginAttempt([
                'username' => $user->username,
                'ip_address' => '127.0.0.1',
                'success' => true,
                'device_info' => "Device {$index}",
            ]);
            $attempt->forceFill(['created_at' => now()->subMinutes($index)])->save();
        }

        $this->actingAs($user)->get(route('user.security'))
            ->assertOk()
            ->assertSee('Page 1 of 2')
            ->assertSee('<details class="login-entry">', false)
            ->assertDontSee('Device 6');
        $this->actingAs($user)->get(route('user.security', ['page' => 2]))
            ->assertOk()
            ->assertSee('Device 6');
    }

    private function makeRequest($owner, string $controlNumber, string $description): void
    {
        ServiceRequest::create([
            'control_number' => $controlNumber,
            'user_id' => $owner->id,
            'requester_name' => $owner->full_name,
            'requester_office' => $owner->office,
            'office' => $owner->office,
            'services' => ['troubleshoot_pc'],
            'description' => $description,
            'status' => 'pending',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
        ]);
    }
}
