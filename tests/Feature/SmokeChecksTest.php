<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SmokeChecksTest extends TestCase
{
    use DatabaseTransactions;

    public function test_application_boots_and_user_can_sign_in_and_load_dashboard(): void
    {
        $user = $this->createUser();

        $this->get('/')
            ->assertOk();

        $this->get('/login')
            ->assertOk();

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'StrongTestPassword!2026',
        ])
            ->assertRedirect(route('dashboard'));

        $this->get('/dashboard')
            ->assertOk();
    }

    public function test_client_pages_render_with_the_shared_responsive_theme(): void
    {
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);

        $user = $this->createUser();
        $this->actingAs($user);

        $routes = [
            route('dashboard'),
            route('requests.index'),
            route('requests.create'),
            route('incident-reports.index'),
            route('incident-reports.create'),
            route('pm-checklists.index'),
            route('user.messenger'),
            route('user.notifications.index'),
            route('user.profile'),
            route('user.settings'),
            route('user.security'),
        ];

        foreach ($routes as $route) {
            $this->get($route)
                ->assertOk()
                ->assertSee('id="mainContent"', false)
                ->assertSee('font-size: 16px', false)
                ->assertSee('-webkit-text-size-adjust: 100%', false);
        }
    }
}
