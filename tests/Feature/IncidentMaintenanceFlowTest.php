<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\IctIncidentReport;
use App\Models\Notification;
use App\Models\PreventiveMaintenanceChecklist;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class IncidentMaintenanceFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);
    }

    public function test_client_incident_does_not_create_maintenance_record_and_still_notifies_admin(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $client = $this->createUser(['designation' => 'Faculty']);

        $this->actingAs($client)
            ->post(route('incident-reports.store'), [
                'date_of_incident' => now()->toDateString(),
                'time_of_incident' => '09:30',
                'nature_of_incident' => ['hardware_failure'],
                'details_of_incident' => 'The laboratory computer unexpectedly powered off.',
            ])
            ->assertRedirect();

        $report = IctIncidentReport::where('user_id', $client->id)->firstOrFail();

        $this->assertFalse(PreventiveMaintenanceChecklist::where('incident_report_id', $report->id)->exists());
        $this->assertFalse(PreventiveMaintenanceChecklist::where('user_id', $client->id)->exists());
        $this->assertTrue(Notification::where('user_id', $admin->id)
            ->where('notification_type', 'admin_action_required')
            ->where('action_url', route('admin.incident-reports.show', $report))
            ->exists());
    }

    public function test_admin_can_create_and_edit_independent_maintenance_details(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $client = $this->createUser(['designation' => 'Faculty']);

        $this->actingAs($admin)
            ->post(route('admin.pm-checklists.store'), [
                'user_id' => $client->id,
                'date_inspected' => now()->toDateString(),
                'system_owner' => $client->full_name,
                'office_department' => $client->office,
                'ict_type' => 'computer',
                'computer_type' => 'desktop',
                'hardware_checks' => ['remove_dust'],
                'status' => 'pending',
            ])
            ->assertRedirect();

        $checklist = PreventiveMaintenanceChecklist::where('user_id', $client->id)->firstOrFail();
        $this->assertNull($checklist->incident_report_id);

        $this->actingAs($admin)
            ->put(route('admin.pm-checklists.update', $checklist), [
                'user_id' => $client->id,
                'date_inspected' => now()->toDateString(),
                'system_owner' => $client->full_name,
                'office_department' => $client->office,
                'ict_type' => 'computer',
                'computer_type' => 'desktop',
                'hardware_checks' => ['remove_dust', 'check_power'],
                'findings' => 'Dust buildup and an unstable power cable were observed.',
                'recommendations' => 'Clean the unit and replace the power cable.',
                'inspected_by_name' => 'MIS TEST STAFF',
                'inspected_by_role' => 'Technical Staff',
                'status' => 'completed',
            ])
            ->assertRedirect(route('admin.pm-checklists.show', $checklist))
            ->assertSessionHasNoErrors();

        $checklist->refresh();
        $this->assertSame('Dust buildup and an unstable power cable were observed.', $checklist->findings);
        $this->assertSame('Clean the unit and replace the power cable.', $checklist->recommendations);
        $this->assertSame('MIS TEST STAFF', $checklist->inspected_by_name);
        $this->assertSame('completed', $checklist->status);
        $this->assertNull($checklist->incident_report_id);
    }

    public function test_owner_can_generate_independent_incident_and_maintenance_pdfs(): void
    {
        $client = $this->createUser(['designation' => 'Faculty']);
        [$report, $checklist] = $this->incidentAndChecklist($client);

        $incidentResponse = $this->actingAs($client)
            ->get(route('incident-reports.pdf', $report))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertPdfIsNotCacheable($incidentResponse->headers->get('Cache-Control'));

        $maintenanceResponse = $this->get(route('pm-checklists.pdf', $checklist))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertPdfIsNotCacheable($maintenanceResponse->headers->get('Cache-Control'));
    }

    public function test_another_client_cannot_view_or_download_independent_records(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        [$report, $checklist] = $this->incidentAndChecklist($owner);

        $this->actingAs($other)
            ->get(route('incident-reports.show', $report))
            ->assertForbidden();
        $this->get(route('incident-reports.pdf', $report))
            ->assertForbidden();
        $this->get(route('pm-checklists.show', $checklist))
            ->assertForbidden();
        $this->get(route('pm-checklists.pdf', $checklist))
            ->assertForbidden();
    }

    private function incidentAndChecklist($client): array
    {
        $report = IctIncidentReport::create([
            'user_id' => $client->id,
            'reported_by' => $client->full_name,
            'department' => $client->office,
            'reporter_email' => $client->email,
            'date_of_incident' => now()->toDateString(),
            'nature_of_incident' => ['hardware_failure'],
            'details_of_incident' => 'Automated incident and PDF verification.',
            'acknowledged_by_name' => $client->full_name,
            'status' => 'open',
        ]);
        $checklist = PreventiveMaintenanceChecklist::create([
            'user_id' => $client->id,
            'date_inspected' => now()->toDateString(),
            'system_owner' => $client->full_name,
            'office_department' => $client->office,
            'ict_type' => 'computer',
            'findings' => $report->details_of_incident,
            'status' => 'pending',
        ]);

        return [$report, $checklist];
    }

    private function assertPdfIsNotCacheable(?string $cacheControl): void
    {
        $this->assertNotNull($cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
    }
}
