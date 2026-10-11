<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminUserDirectoryPaginationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_page_through_users_without_changing_overall_counts(): void
    {
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);

        $oldest = $this->createUser();
        for ($index = 0; $index < 19; $index++) {
            $this->createUser();
        }
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);

        $pageOne = $this->actingAs($admin)->get(route('admin.users.index'));
        $pageOne->assertOk()
            ->assertSee('aria-label="User directory pages"', false)
            ->assertSee('rel="next"', false)
            ->assertDontSee($oldest->email);
        $pageOne->assertViewHas('users', fn ($users) => $users->count() === 15
            && $users->total() === User::count());

        $this->get(route('admin.users.index', ['page' => 2]))
            ->assertOk()
            ->assertSee($oldest->email)
            ->assertSee('rel="prev"', false);
    }
}
