<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\ServiceRequest;
use App\Models\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class RequestSubmissionApprovalFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);
    }

    public function test_user_request_submission_and_admin_approval_flow_is_validated_end_to_end(): void
    {
        $owner = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $technician = Technician::create([
            'email' => Str::lower(Str::random(10)).'@example.test',
            'first_name' => 'Test',
            'last_name' => 'Technician',
            'job_assignment' => 'Technical Staff',
            'status' => 'available',
            'is_active' => true,
            'current_workload' => 0,
        ]);

        $this->assertDatabaseCount('service_requests', 0);

        $this->actingAs($owner)
            ->post('/requests', [
                'requester_name' => $owner->full_name,
                'requester_office' => $owner->office,
                'services' => ['troubleshoot_pc'],
                'description' => 'The desktop cannot connect to the campus network.',
                'property_no' => 'TEST-ICT-001',
                'date_request' => now()->toDateString(),
                'time_request' => now()->format('H:i'),
            ])
            ->assertRedirect(route('dashboard'));

        $request = ServiceRequest::where('user_id', $owner->id)->firstOrFail();
        $this->assertDatabaseHas('service_requests', ['id' => $request->id, 'status' => 'pending']);

        $this->actingAs($owner)
            ->post('/requests', [
                'requester_name' => $owner->full_name,
                'requester_office' => $owner->office,
                'services' => [],
                'description' => 'Bad validation payload.',
                'property_no' => 'TEST-ICT-002',
                'date_request' => now()->toDateString(),
                'time_request' => now()->format('H:i'),
            ])
            ->assertSessionHasErrors('services');

        $this->assertDatabaseCount('service_requests', 1);

        $this->actingAs($admin)
            ->from(route('admin.service-requests.show', $request))
            ->post(route('admin.service-requests.triage', $request), [
                'assigned_technician_id' => $technician->id,
                'technician_notes' => 'Investigate network access.',
            ])
            ->assertRedirect(route('admin.service-requests.show', $request));

        $this->actingAs($admin)
            ->from(route('admin.service-requests.show', $request))
            ->post(route('admin.service-requests.update-status', $request), [
                'status' => 'processing',
                'remarks' => 'Ticket assigned to technician.',
            ])
            ->assertRedirect(route('admin.service-requests.show', $request));

        $this->actingAs($admin)
            ->from(route('admin.service-requests.show', $request))
            ->post(route('admin.service-requests.mark-completed', $request), [
                'comments' => 'Network access restored.',
            ])
            ->assertRedirect(route('admin.service-requests.show', $request));

        $request->refresh();
        $this->assertSame('awaiting_confirmation', $request->status);

        $this->actingAs($owner)
            ->from(route('requests.show', $request))
            ->post(route('requests.feedback', $request), [
                'feedback_rating' => 5,
                'feedback_resolved' => true,
                'feedback_comment' => 'Everything is working now.',
            ])
            ->assertRedirect(route('requests.show', $request));

        $request->refresh();
        $this->assertSame('closed', $request->status);
        $this->assertSame(5, $request->feedback_rating);
    }
}
