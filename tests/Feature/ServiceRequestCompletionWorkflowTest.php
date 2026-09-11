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

class ServiceRequestCompletionWorkflowTest extends TestCase
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

    public function test_unassigned_request_cannot_be_marked_complete(): void
    {
        $admin = $this->admin();
        $serviceRequest = $this->requestWithStatus('pending');

        $this->actingAs($admin)
            ->from(route('admin.service-requests.show', $serviceRequest))
            ->post(route('admin.service-requests.mark-completed', $serviceRequest), [
                'comments' => 'Work completed.',
            ])
            ->assertRedirect(route('admin.service-requests.show', $serviceRequest))
            ->assertSessionHasErrors('assigned_technician_id');

        $this->assertSame('pending', $serviceRequest->fresh()->status);
    }

    public function test_assigned_reopened_request_must_resume_work_before_completion(): void
    {
        $admin = $this->admin();
        $technician = $this->technician();
        $serviceRequest = $this->requestWithStatus('reopened', $technician);

        $this->actingAs($admin)
            ->from(route('admin.service-requests.show', $serviceRequest))
            ->post(route('admin.service-requests.mark-completed', $serviceRequest), [
                'comments' => 'Work completed.',
            ])
            ->assertRedirect(route('admin.service-requests.show', $serviceRequest))
            ->assertSessionHasErrors('status');

        $this->assertSame('reopened', $serviceRequest->fresh()->status);
    }

    public function test_assigned_in_progress_request_can_move_to_requester_confirmation(): void
    {
        $admin = $this->admin();
        $technician = $this->technician();
        $serviceRequest = $this->requestWithStatus('processing', $technician);

        $this->actingAs($admin)
            ->from(route('admin.service-requests.show', $serviceRequest))
            ->post(route('admin.service-requests.mark-completed', $serviceRequest), [
                'comments' => 'Network access restored.',
            ])
            ->assertRedirect(route('admin.service-requests.show', $serviceRequest))
            ->assertSessionHasNoErrors();

        $serviceRequest->refresh();
        $this->assertSame('awaiting_confirmation', $serviceRequest->status);
        $this->assertNotNull($serviceRequest->work_completed_at);
    }

    public function test_generic_status_action_cannot_skip_directly_to_completed(): void
    {
        $admin = $this->admin();
        $technician = $this->technician();
        $serviceRequest = $this->requestWithStatus('processing', $technician);

        $this->actingAs($admin)
            ->from(route('admin.service-requests.show', $serviceRequest))
            ->post(route('admin.service-requests.update-status', $serviceRequest), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('admin.service-requests.show', $serviceRequest))
            ->assertSessionHasErrors('status');

        $this->assertSame('processing', $serviceRequest->fresh()->status);
    }

    private function admin()
    {
        return $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    private function technician(): Technician
    {
        return Technician::create([
            'email' => Str::lower(Str::random(10)).'@example.test',
            'first_name' => 'Test',
            'last_name' => 'Technician',
            'job_assignment' => 'Technical Staff',
            'status' => 'available',
            'is_active' => true,
            'current_workload' => 0,
        ]);
    }

    private function requestWithStatus(string $status, ?Technician $technician = null): ServiceRequest
    {
        $owner = $this->createUser();

        return ServiceRequest::create([
            'control_number' => 'TEST-WORKFLOW-'.Str::upper(Str::random(8)),
            'user_id' => $owner->id,
            'requester_name' => $owner->full_name,
            'requester_office' => $owner->office,
            'office' => $owner->office,
            'services' => ['troubleshoot_pc'],
            'description' => 'Automated completion workflow test.',
            'status' => $status,
            'assigned_technician_id' => $technician?->id,
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
        ]);
    }
}
