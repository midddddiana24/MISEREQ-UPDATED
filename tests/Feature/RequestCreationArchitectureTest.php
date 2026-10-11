<?php

namespace Tests\Feature;

use App\Jobs\NotifyAdminNewRequest;
use App\Jobs\SendRequestCreatedNotification;
use App\Models\AdminActivityLog;
use App\Models\Notification;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RequestCreationArchitectureTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('secure');
    }

    public function test_user_route_creates_request_redirects_and_dispatches_notifications(): void
    {
        Queue::fake();
        $user = $this->createUser();
        $this->createUser(['role' => 'admin', 'is_admin' => true]);

        $response = $this->actingAs($user)->post(route('requests.store'), [
            'requester_name' => 'Route User',
            'services' => ['troubleshoot_pc'],
            'description' => 'User route architecture test.',
            'property_no' => 'USER-001',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('service_requests', [
            'user_id' => $user->id,
            'requester_name' => 'Route User',
            'status' => 'pending',
            'property_no' => 'USER-001',
        ]);
        $request = ServiceRequest::where('user_id', $user->id)->latest('id')->firstOrFail();
        Queue::assertPushed(SendRequestCreatedNotification::class);
        Queue::assertPushed(NotifyAdminNewRequest::class);
        $this->assertDatabaseHas('notifications', [
            'service_request_id' => $request->id,
            'notification_type' => 'admin_action_required',
        ]);
    }

    public function test_admin_on_behalf_route_creates_request_and_logs_activity(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.service-requests.store-on-behalf'), [
            'requester_name' => 'Walk-in User',
            'requester_office' => 'Library',
            'services' => ['account_creation'],
            'description' => 'Admin route architecture test.',
        ]);

        $request = ServiceRequest::where('requester_name', 'Walk-in User')->latest('id')->firstOrFail();
        $response->assertRedirect(route('admin.service-requests.show', $request));
        $this->assertDatabaseHas('service_requests', [
            'id' => $request->id,
            'created_by' => $admin->id,
            'is_admin_created' => 1,
            'status' => 'submitted',
        ]);
        $this->assertDatabaseHas('admin_activity_logs', [
            'service_request_id' => $request->id,
            'action' => 'walk_in_request_created',
            'actor_id' => $admin->id,
            'description' => 'Account-free walk-in request encoded by MIS.',
        ]);
        $activity = AdminActivityLog::where('service_request_id', $request->id)
            ->where('action', 'walk_in_request_created')
            ->firstOrFail();
        $this->assertSame([
            'requester_name' => 'Walk-in User',
            'requester_office' => 'Library',
            'phone' => null,
        ], $activity->new_values);
    }

    public function test_user_route_validation_rejects_missing_services_without_creating_row(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson(route('requests.store'), ['description' => 'Invalid user request.'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('services');

        $this->assertDatabaseMissing('service_requests', [
            'description' => 'Invalid user request.',
        ]);
    }

    public function test_admin_route_validation_rejects_missing_requester_without_creating_row(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);

        $this->actingAs($admin)
            ->postJson(route('admin.service-requests.store-on-behalf'), [
                'services' => ['account_creation'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('requester_name');

        $this->assertDatabaseMissing('service_requests', [
            'status' => 'submitted',
            'is_admin_created' => 1,
        ]);
    }

    public function test_user_route_merges_single_attachment_field_into_shared_action(): void
    {
        $user = $this->createUser();
        $singleFile = UploadedFile::fake()->create('user-attachment.pdf', 10, 'application/pdf');
        $arrayFile = UploadedFile::fake()->create('user-array-attachment.pdf', 10, 'application/pdf');

        $this->actingAs($user)->post(route('requests.store'), [
            'services' => ['troubleshoot_pc'],
            'attachments' => [$arrayFile],
            'attachment' => $singleFile,
        ])->assertRedirect(route('dashboard'));

        $request = ServiceRequest::where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertDatabaseHas('service_request_attachments', [
            'service_request_id' => $request->id,
            'original_name' => 'user-attachment.pdf',
            'uploaded_by' => $user->id,
        ]);
        $this->assertDatabaseHas('service_request_attachments', [
            'service_request_id' => $request->id,
            'original_name' => 'user-array-attachment.pdf',
            'uploaded_by' => $user->id,
        ]);
        $this->assertSame(2, $request->attachments()->count());
    }

    public function test_admin_route_persists_array_attachments_through_shared_action(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $file = UploadedFile::fake()->create('admin-attachment.pdf', 10, 'application/pdf');

        $this->actingAs($admin)->post(route('admin.service-requests.store-on-behalf'), [
            'requester_name' => 'Attachment Walk-in',
            'services' => ['account_creation'],
            'attachments' => [$file],
        ])->assertRedirect();

        $request = ServiceRequest::where('requester_name', 'Attachment Walk-in')->latest('id')->firstOrFail();
        $this->assertDatabaseHas('service_request_attachments', [
            'service_request_id' => $request->id,
            'original_name' => 'admin-attachment.pdf',
            'uploaded_by' => $admin->id,
        ]);
    }

    public function test_regular_authenticated_user_cannot_post_to_admin_on_behalf_route(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson(route('admin.service-requests.store-on-behalf'), [
                'requester_name' => 'Should not be created',
                'services' => ['account_creation'],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('service_requests', [
            'requester_name' => 'Should not be created',
        ]);
    }

    public function test_locked_user_is_denied_by_the_user_creation_policy(): void
    {
        $user = $this->createUser([
            'locked_until' => now()->addMinutes(10),
        ]);

        $this->actingAs($user)
            ->postJson(route('requests.store'), [
                'requester_name' => 'Locked account request',
                'services' => ['account_creation'],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('service_requests', [
            'requester_name' => 'Locked account request',
        ]);
    }

    public function test_extra_mass_assignment_fields_are_ignored_by_both_creation_routes(): void
    {
        $user = $this->createUser();
        $this->actingAs($user)->post(route('requests.store'), [
            'services' => ['troubleshoot_pc'],
            'status' => 'completed',
            'id' => 999999,
            'created_at' => '2000-01-01 00:00:00',
        ])->assertRedirect(route('dashboard'));

        $userRequest = ServiceRequest::where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertSame('pending', $userRequest->status);
        $this->assertNotSame(999999, $userRequest->id);

        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $this->actingAs($admin)->post(route('admin.service-requests.store-on-behalf'), [
            'requester_name' => 'Mass Assignment Walk-in',
            'services' => ['account_creation'],
            'status' => 'completed',
            'id' => 999998,
            'created_at' => '2000-01-01 00:00:00',
        ])->assertRedirect();

        $adminRequest = ServiceRequest::where('requester_name', 'Mass Assignment Walk-in')->latest('id')->firstOrFail();
        $this->assertSame('submitted', $adminRequest->status);
        $this->assertNotSame(999998, $adminRequest->id);
    }
}
