<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\AdminActivityLog;
use App\Models\IctIncidentReport;
use App\Models\Notification;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminRecordListFilteringTest extends TestCase
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

    public function test_admin_can_archive_an_active_service_request_and_restore_it(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $owner = $this->createUser();
        $request = ServiceRequest::create([
            'control_number' => 'REQ-ARCHIVE-'.Str::upper(Str::random(8)),
            'user_id' => $owner->id,
            'requester_name' => $owner->full_name,
            'services' => ['troubleshoot_pc'],
            'status' => 'pending',
            'date_of_request' => now()->toDateString(),
        ]);
        $reason = 'Duplicate active request; the requester will use the original ticket.';

        $this->actingAs($admin)->get(route('admin.requests.index', ['search' => $request->control_number]))
            ->assertOk()
            ->assertSee(route('admin.requests.archive', $request))
            ->assertSee('Archive');

        $this->post(route('admin.requests.archive', $request), ['archive_reason' => $reason])
            ->assertRedirect(route('admin.requests.index'));
        $this->assertSoftDeleted($request);
        $archived = ServiceRequest::withTrashed()->findOrFail($request->id);
        $this->assertSame($reason, $archived->archive_reason);
        $this->assertSame($admin->id, $archived->archived_by);
        $this->assertTrue(AdminActivityLog::where('service_request_id', $request->id)->where('action', 'request_archived')->exists());
        $this->assertTrue(Notification::where('service_request_id', $request->id)->where('user_id', $owner->id)->where('subject', 'Service request archived')->exists());

        $this->get(route('admin.requests.index', ['search' => $request->control_number]))
            ->assertOk()->assertViewHas('requests', fn ($requests) => $requests->total() === 0);
        $this->get(route('admin.requests.index', ['search_queue' => 'archived', 'search' => $request->control_number]))
            ->assertOk()->assertViewHas('requests', fn ($requests) => $requests->total() === 1 && $requests->first()->id === $request->id);

        $this->post(route('admin.requests.restore', $request))
            ->assertRedirect(route('admin.requests.index', ['search_queue' => 'archived']));
        $this->assertFalse($request->fresh()->trashed());
        $this->assertNull($request->fresh()->archive_reason);
    }

    public function test_admin_can_replace_legacy_incident_and_maintenance_statuses_with_four_stage_values(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $incident = IctIncidentReport::create([
            'document_number' => 'IIR-STAGE-'.Str::upper(Str::random(8)),
            'reported_by' => 'Campus reporter',
            'date_of_incident' => now()->toDateString(),
            'status' => 'open',
        ]);
        $maintenance = PreventiveMaintenanceChecklist::create([
            'control_number' => 'PMC-STAGE-'.Str::upper(Str::random(8)),
            'system_owner' => 'Campus owner',
            'date_inspected' => now()->toDateString(),
            'status' => 'needs_action',
        ]);

        $this->actingAs($admin)->get(route('admin.incident-reports.edit', $incident))
            ->assertOk()->assertSee('value="processing"', false);
        $this->get(route('admin.pm-checklists.edit', $maintenance))
            ->assertOk()->assertSee('value="processing"', false);

        $this->put(route('admin.incident-reports.update', $incident), [
            'reported_by' => 'Campus reporter',
            'received_by' => 'MIS staff',
            'date_of_incident' => now()->toDateString(),
            'status' => 'processing',
        ])->assertRedirect(route('admin.incident-reports.show', $incident))->assertSessionHasNoErrors();
        $this->assertSame('processing', $incident->fresh()->status);

        $this->put(route('admin.pm-checklists.update', $maintenance), [
            'system_owner' => 'Campus owner',
            'date_inspected' => now()->toDateString(),
            'ict_type' => 'computer',
            'status' => 'completed',
        ])->assertRedirect(route('admin.pm-checklists.show', $maintenance))->assertSessionHasNoErrors();
        $this->assertSame('completed', $maintenance->fresh()->status);
    }

    public function test_incident_search_status_date_and_archived_filters_keep_results_separate(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $marker = 'IIR-QA-'.Str::upper(Str::random(8));
        $target = IctIncidentReport::create([
            'document_number' => $marker.'-A',
            'reported_by' => 'Library Reporter',
            'department' => 'Library',
            'date_of_incident' => '2026-10-03',
            'status' => 'open',
        ]);
        $other = IctIncidentReport::create([
            'document_number' => $marker.'-B',
            'reported_by' => 'Old Reporter',
            'department' => 'Library',
            'date_of_incident' => '2026-09-01',
            'status' => 'resolved',
        ]);
        $archived = IctIncidentReport::create([
            'document_number' => $marker.'-C',
            'reported_by' => 'Archived Reporter',
            'date_of_incident' => '2026-10-02',
            'status' => 'open',
        ]);
        $archived->delete();

        $this->actingAs($admin)->get(route('admin.incident-reports.index', [
            'search' => $marker,
            'status' => 'open',
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-04',
        ]))->assertOk()
            ->assertSee($target->document_number)
            ->assertDontSee($other->document_number)
            ->assertDontSee($archived->document_number)
            ->assertSee('value="'.$marker.'"', false)
            ->assertSee('value="2026-10-01"', false);

        $this->get(route('admin.incident-reports.index', ['search' => $marker, 'archived' => 1]))
            ->assertOk()
            ->assertSee($archived->document_number)
            ->assertDontSee($target->document_number)
            ->assertDontSee($other->document_number);
        $this->get(route('admin.incident-reports.index', ['search' => $marker, 'group' => 'archived']))
            ->assertOk()->assertSee($archived->document_number)->assertDontSee($target->document_number);

        $this->get(route('admin.incident-reports.index', ['date_to' => '2026-10-04']))
            ->assertOk()->assertSee($target->document_number);

        $this->get(route('admin.incident-reports.index', [
            'date_from' => '2026-10-04', 'date_to' => '2026-10-01',
        ]))->assertSessionHasErrors('date_to');
    }

    public function test_maintenance_search_status_date_and_archived_filters_keep_results_separate(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $marker = 'PMC-QA-'.Str::upper(Str::random(8));
        $target = PreventiveMaintenanceChecklist::create([
            'control_number' => $marker.'-A',
            'system_owner' => 'Library Owner',
            'property_number' => 'LIB-123',
            'date_inspected' => '2026-10-03',
            'status' => 'pending',
        ]);
        $other = PreventiveMaintenanceChecklist::create([
            'control_number' => $marker.'-B',
            'system_owner' => 'Old Owner',
            'date_inspected' => '2026-09-01',
            'status' => 'completed',
        ]);
        $archived = PreventiveMaintenanceChecklist::create([
            'control_number' => $marker.'-C',
            'system_owner' => 'Archived Owner',
            'date_inspected' => '2026-10-02',
            'status' => 'pending',
        ]);
        $archived->delete();

        $this->actingAs($admin)->get(route('admin.pm-checklists.index', [
            'search' => $marker,
            'status' => 'pending',
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-04',
        ]))->assertOk()
            ->assertSee($target->control_number)
            ->assertDontSee($other->control_number)
            ->assertDontSee($archived->control_number)
            ->assertSee('value="'.$marker.'"', false)
            ->assertSee('value="2026-10-01"', false);

        $this->get(route('admin.pm-checklists.index', ['search' => 'LIB-123']))
            ->assertOk()->assertSee($target->control_number)->assertDontSee($other->control_number);

        $this->get(route('admin.pm-checklists.index', ['search' => $marker, 'archived' => 1]))
            ->assertOk()
            ->assertSee($archived->control_number)
            ->assertDontSee($target->control_number)
            ->assertDontSee($other->control_number);
        $this->get(route('admin.pm-checklists.index', ['search' => $marker, 'group' => 'archived']))
            ->assertOk()->assertSee($archived->control_number)->assertDontSee($target->control_number);

        $this->get(route('admin.pm-checklists.index', ['date_to' => '2026-10-04']))
            ->assertOk()->assertSee($target->control_number);

        $this->get(route('admin.pm-checklists.index', [
            'date_from' => '2026-10-04', 'date_to' => '2026-10-01',
        ]))->assertSessionHasErrors('date_to');
    }

    public function test_search_and_status_remain_in_pagination_links_on_both_lists(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $marker = 'PAGEQA'.Str::upper(Str::random(8));
        for ($index = 1; $index <= 16; $index++) {
            IctIncidentReport::create([
                'document_number' => "IIR-{$marker}-{$index}",
                'reported_by' => 'Pagination reporter',
                'date_of_incident' => '2026-10-03',
                'status' => 'open',
            ]);
            PreventiveMaintenanceChecklist::create([
                'control_number' => "PMC-{$marker}-{$index}",
                'system_owner' => 'Pagination owner',
                'date_inspected' => '2026-10-03',
                'status' => 'pending',
            ]);
        }

        $this->actingAs($admin)->get(route('admin.incident-reports.index', [
            'search' => $marker, 'status' => 'open', 'group' => 'needs_review',
        ]))->assertOk()->assertSee('page=2')->assertSee('search='.$marker)->assertSee('status=open')->assertSee('group=needs_review');

        $this->get(route('admin.pm-checklists.index', [
            'search' => $marker, 'status' => 'pending', 'group' => 'needs_review',
        ]))->assertOk()->assertSee('page=2')->assertSee('search='.$marker)->assertSee('status=pending')->assertSee('group=needs_review');
    }

    public function test_workflow_and_linkage_groups_filter_both_lists(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $owner = $this->createUser();
        $marker = 'GROUPQA'.Str::upper(Str::random(8));
        $serviceRequest = ServiceRequest::create([
            'control_number' => "REQ-{$marker}",
            'user_id' => $owner->id,
            'requester_name' => $owner->full_name,
            'services' => ['troubleshoot_pc'],
            'status' => 'pending',
            'date_of_request' => '2026-10-03',
            'time_of_request' => '10:00:00',
        ]);
        $linkedIncident = IctIncidentReport::create([
            'document_number' => "IIR-{$marker}-A",
            'service_request_id' => $serviceRequest->id,
            'reported_by' => 'Linked reporter',
            'date_of_incident' => '2026-10-03',
            'status' => 'open',
        ]);
        $standaloneIncident = IctIncidentReport::create([
            'document_number' => "IIR-{$marker}-B",
            'reported_by' => 'Standalone reporter',
            'date_of_incident' => '2026-10-03',
            'status' => 'in_progress',
        ]);
        $linkedMaintenance = PreventiveMaintenanceChecklist::create([
            'control_number' => "PMC-{$marker}-A",
            'service_request_id' => $serviceRequest->id,
            'system_owner' => 'Linked owner',
            'date_inspected' => '2026-10-03',
            'status' => 'pending',
        ]);
        $standaloneMaintenance = PreventiveMaintenanceChecklist::create([
            'control_number' => "PMC-{$marker}-B",
            'system_owner' => 'Standalone owner',
            'date_inspected' => '2026-10-03',
            'status' => 'needs_action',
        ]);

        foreach ([
            'linked' => [$linkedIncident->document_number, $standaloneIncident->document_number],
            'needs_review' => [$linkedIncident->document_number, $standaloneIncident->document_number],
            'standalone' => [$standaloneIncident->document_number, $linkedIncident->document_number],
            'in_progress' => [$standaloneIncident->document_number, $linkedIncident->document_number],
        ] as $group => [$included, $excluded]) {
            $this->actingAs($admin)->get(route('admin.incident-reports.index', ['group' => $group, 'search' => $marker]))
                ->assertOk()->assertSee($included)->assertDontSee($excluded);
        }

        foreach ([
            'linked' => [$linkedMaintenance->control_number, $standaloneMaintenance->control_number],
            'needs_review' => [$linkedMaintenance->control_number, $standaloneMaintenance->control_number],
            'standalone' => [$standaloneMaintenance->control_number, $linkedMaintenance->control_number],
            'needs_action' => [$standaloneMaintenance->control_number, $linkedMaintenance->control_number],
        ] as $group => [$included, $excluded]) {
            $this->get(route('admin.pm-checklists.index', ['group' => $group, 'search' => $marker]))
                ->assertOk()->assertSee($included)->assertDontSee($excluded);
        }

        $this->get(route('admin.incident-reports.index', ['group' => 'invalid']))
            ->assertSessionHasErrors('group');
        $this->get(route('admin.pm-checklists.index', ['group' => 'invalid']))
            ->assertSessionHasErrors('group');
    }

    public function test_admin_stage_filters_include_existing_detailed_request_and_legacy_record_statuses(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $marker = 'STAGEQA'.Str::upper(Str::random(8));
        $request = ServiceRequest::create([
            'control_number' => "REQ-{$marker}",
            'requester_name' => 'Stage Test',
            'services' => ['troubleshoot_pc'],
            'status' => 'awaiting_requester_info',
            'date_of_request' => now()->toDateString(),
        ]);
        $incident = IctIncidentReport::create([
            'document_number' => "IIR-{$marker}",
            'reported_by' => 'Stage Test',
            'date_of_incident' => now()->toDateString(),
            'status' => 'in_progress',
        ]);
        $maintenance = PreventiveMaintenanceChecklist::create([
            'control_number' => "PMC-{$marker}",
            'system_owner' => 'Stage Test',
            'date_inspected' => now()->toDateString(),
            'status' => 'needs_action',
        ]);

        $this->actingAs($admin)->get(route('admin.requests.index', ['search' => $marker, 'search_stage' => 'pending']))
            ->assertOk()->assertSee($request->control_number)->assertSee('Waiting for User Reply');
        $this->get(route('admin.requests.index', ['search' => $marker, 'search_stage' => 'completed']))
            ->assertOk()->assertDontSee($request->control_number);
        $this->get(route('admin.incident-reports.index', ['search' => $marker, 'status' => 'processing']))
            ->assertOk()->assertSee($incident->document_number)->assertSee('Processing');
        $this->get(route('admin.pm-checklists.index', ['search' => $marker, 'status' => 'processing']))
            ->assertOk()->assertSee($maintenance->control_number)->assertSee('Processing');
    }
}
