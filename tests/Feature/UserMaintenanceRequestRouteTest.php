<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\PreventiveMaintenanceChecklist;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserMaintenanceRequestRouteTest extends TestCase
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

    public function test_user_links_and_legacy_url_open_the_maintenance_form(): void
    {
        $user = $this->createUser();
        $url = route('pm-checklists.create');

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()->assertSee('href="'.$url.'"', false);
        $this->get(route('pm-checklists.index'))
            ->assertOk()->assertSee('href="'.$url.'" class="button">Request Maintenance</a>', false);
        $this->get(route('requests.create', ['service' => 'preventive_maintenance']))
            ->assertRedirect($url);
        $this->get($url)->assertOk()
            ->assertSee('Request Preventive Maintenance')
            ->assertSee('action="'.route('pm-checklists.store').'"', false);
    }

    public function test_submission_creates_submitted_checklist_for_admin_review(): void
    {
        $user = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);

        $this->actingAs($user)->post(route('pm-checklists.store'), [
            'date_inspected' => now()->toDateString(),
            'system_owner' => $user->full_name,
            'office_department' => 'Library',
            'ict_type' => 'computer',
            'computer_type' => 'desktop',
            'hardware_checks' => ['remove_dust'],
            'findings' => 'Dust around the vents',
        ])->assertSessionHasNoErrors();

        $checklist = PreventiveMaintenanceChecklist::where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertSame('submitted', $checklist->status);
        $this->assertSame(['remove_dust'], $checklist->hardware_checks);
        $this->actingAs($admin)->get(route('admin.pm-checklists.index'))
            ->assertOk()->assertSee($checklist->control_number);
    }

    public function test_guest_and_invalid_submissions_are_rejected(): void
    {
        $this->get(route('pm-checklists.create'))->assertRedirect(route('login'));
        $this->post(route('pm-checklists.store'), [])->assertRedirect(route('login'));

        $user = $this->createUser();
        $this->actingAs($user)->post(route('pm-checklists.store'), [
            'date_inspected' => now()->toDateString(),
            'system_owner' => $user->full_name,
            'ict_type' => 'computer',
            'computer_type' => 'desktop',
            'hardware_checks' => ['invalid_check'],
        ])->assertSessionHasErrors('hardware_checks.0');
        $this->assertFalse(PreventiveMaintenanceChecklist::where('user_id', $user->id)->exists());
    }
}
