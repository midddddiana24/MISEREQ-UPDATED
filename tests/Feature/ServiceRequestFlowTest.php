<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ServiceRequestFlowTest extends TestCase
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

    public function test_client_can_submit_and_view_a_service_request(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->post('/requests', [
                'requester_name' => $user->full_name,
                'requester_office' => $user->office,
                'services' => ['troubleshoot_pc'],
                'description' => 'The desktop cannot connect to the campus network.',
                'property_no' => 'TEST-ICT-001',
                'date_request' => now()->toDateString(),
                'time_request' => now()->format('H:i'),
            ])
            ->assertRedirect(route('dashboard'));

        $request = ServiceRequest::where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertSame('pending', $request->status);

        $this->actingAs($user)
            ->get(route('requests.show', $request))
            ->assertOk()
            ->assertSee($request->control_number);
    }

    public function test_client_cannot_view_another_users_request(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $request = $this->makeRequest($owner);

        $this->actingAs($other)
            ->get(route('requests.show', $request))
            ->assertForbidden();
    }

    public function test_owner_can_send_message_but_another_client_cannot(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $request = $this->makeRequest($owner);

        $this->actingAs($owner)
            ->postJson('/api/messages/send', [
                'service_request_id' => $request->id,
                'message' => 'Please check this request when available.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($other)
            ->postJson('/api/messages/send', [
                'service_request_id' => $request->id,
                'message' => 'Unauthorized message.',
            ])
            ->assertForbidden();
    }

    public function test_owner_can_generate_official_pdf(): void
    {
        $owner = $this->createUser();
        $request = $this->makeRequest($owner);
        $cacheKey = 'pdf_v11_' . $request->id . '_' . $request->updated_at->timestamp;

        // Match production's database default. PDF bytes must still use the
        // dedicated file store instead of being inserted into MySQL.
        config(['cache.default' => 'database']);

        try {
            $this->actingAs($owner)
                ->get(route('request.pdf', $request))
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf')
                ->assertHeader('Content-Disposition');

            $this->actingAs($owner)
                ->get(route('request.download-pdf', $request))
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf')
                ->assertDownload('MIS-Request-' . $request->control_number . '.pdf');
        } finally {
            Cache::store('file')->forget($cacheKey);
        }
    }

    public function test_stale_admin_action_is_rejected_when_request_changed_since_load(): void
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);
        $request = ServiceRequest::create([
            'control_number' => 'STALE-ADMIN-' . now()->format('YmdHisv'),
            'user_id' => $this->createUser()->id,
            'requester_name' => 'Stale Reviewer',
            'requester_office' => 'MIS Office',
            'office' => 'MIS Office',
            'services' => ['troubleshoot_pc'],
            'description' => 'Two admins act on the same request at once.',
            'status' => 'submitted',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
        ]);
        $staleVersion = $request->version;

        $request->update([
            'status' => 'pending',
            'assigned_technician_id' => null,
            'version' => $staleVersion + 1,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.service-requests.show', $request))
            ->post(route('admin.service-requests.triage', $request), [
                'assigned_technician_id' => null,
                'technician_notes' => 'Second admin attempt with stale state.',
                'version' => $staleVersion,
            ])
            ->assertSessionHasErrors('version')
            ->assertSessionHas('errors', function ($errors) {
                return str_contains((string) $errors->first('version'), 'already updated by someone else');
            });

        $request->refresh();
        $this->assertSame('pending', $request->status);
        $this->assertNull($request->technician_notes);
    }

    private function makeRequest($owner): ServiceRequest
    {
        return ServiceRequest::create([
            'control_number' => 'TEST-' . $owner->id . '-' . now()->format('Hisv'),
            'user_id' => $owner->id,
            'requester_name' => $owner->full_name,
            'requester_office' => $owner->office,
            'office' => $owner->office,
            'services' => ['troubleshoot_pc'],
            'description' => 'Automated service request workflow test.',
            'status' => 'pending',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
        ]);
    }
}
