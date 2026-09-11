<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SubmissionFeedbackTest extends TestCase
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

    public function test_submission_feedback_is_loaded_on_public_client_and_admin_pages(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-mis-submit-feedback', false)
            ->assertSee('Processing your request...');

        $client = $this->createUser();
        $this->actingAs($client)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-mis-submit-feedback', false);

        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-mis-submit-feedback', false);
    }
}
