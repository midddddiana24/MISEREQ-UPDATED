<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UserInterfaceEnhancementsTest extends TestCase
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

    public function test_recent_activity_uses_the_request_description_and_a_readable_service_fallback(): void
    {
        $user = $this->createUser();
        $this->actingAs($user)->post(route('requests.store'), [
            'services' => ['troubleshoot_pc'],
            'description' => 'The library computer cannot connect to Wi-Fi.',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('The library computer cannot connect to Wi-Fi.');

        $request = ServiceRequest::where('user_id', $user->id)->latest('id')->firstOrFail();
        $request->update(['description' => null]);
        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Troubleshoot PC');
    }

    public function test_service_categories_keep_multiple_selections_and_save_their_extra_details_on_create_and_edit(): void
    {
        $user = $this->createUser();
        $this->actingAs($user)->get(route('requests.create'))
            ->assertOk()
            ->assertSee('<details class="service-box service-category"', false)
            ->assertSee('Open a category, then select one or more services.');

        $this->actingAs($user)->post(route('requests.store'), [
            'services' => ['others_network', 'others_ict', 'others_mis'],
            'network_specify_location' => 'Library Room 204',
            'network_other' => 'Extend the wired connection',
            'ict_other' => 'Inspect the barcode scanner',
            'mis_other' => 'Update staff permissions',
        ])->assertRedirect(route('dashboard'));

        $request = ServiceRequest::where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertSame('Library Room 204', $request->office_location);
        $this->assertSame('Extend the wired connection', $request->network_others);
        $this->assertSame('Inspect the barcode scanner', $request->ict_others);
        $this->assertSame('Update staff permissions', $request->mis_others);

        $this->actingAs($user)->get(route('requests.show', $request))
            ->assertOk()
            ->assertSee('Library Room 204')
            ->assertSee('Update staff permissions');

        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.service-requests.show', $request))
            ->assertOk()
            ->assertSee('Library Room 204')
            ->assertSee('Update staff permissions');

        $edit = $this->actingAs($user)->get(route('requests.edit', $request))
            ->assertOk()
            ->assertSee('value="Extend the wired connection"', false)
            ->assertSee('value="Library Room 204"', false);
        preg_match_all('/<details class="service-box service-category"([^>]*)>/', $edit->getContent(), $categories);
        $this->assertCount(3, $categories[1]);
        foreach ($categories[1] as $attributes) {
            $this->assertStringContainsString('open', $attributes);
        }

        $this->actingAs($user)->patch(route('requests.update', $request), [
            'services' => ['others_network', 'others_ict'],
            'network_specify_location' => 'MIS Office',
            'network_other' => 'Replace the damaged cable',
            'ict_other' => 'Check the scanner driver',
        ])->assertRedirect(route('requests.show', $request));

        $this->assertSame('MIS Office', $request->fresh()->office_location);
        $this->assertSame('Replace the damaged cable', $request->fresh()->network_others);
        $this->assertSame('Check the scanner driver', $request->fresh()->ict_others);
        $this->assertNull($request->fresh()->mis_others);
    }
}
