<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessageConversationDiscoveryTest extends TestCase
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

    public function test_new_request_is_immediately_visible_as_a_client_conversation(): void
    {
        $client = $this->createUser();
        $serviceRequest = $this->makeRequest($client);

        $this->actingAs($client)
            ->get(route('user.messenger'))
            ->assertOk()
            ->assertSee($serviceRequest->control_number)
            ->assertSee('Conversation ready')
            ->assertSee('Open this conversation to send the first message about your request.');
    }

    public function test_new_account_linked_request_is_immediately_visible_to_admin_messenger(): void
    {
        $client = $this->createUser();
        $serviceRequest = $this->makeRequest($client);
        $admin = $this->createUser([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.messenger'))
            ->assertOk()
            ->assertSee($serviceRequest->control_number)
            ->assertSee('No messages yet');
    }

    public function test_sent_message_remains_visible_after_messenger_refresh(): void
    {
        $client = $this->createUser();
        $serviceRequest = $this->makeRequest($client);

        $this->actingAs($client)
            ->postJson('/api/messages/send', [
                'service_request_id' => $serviceRequest->id,
                'message' => 'Please check the network outlet in our office.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($client)
            ->get(route('user.messenger'))
            ->assertOk()
            ->assertSee('Please check the network outlet in our office.');

        $this->actingAs($client)
            ->getJson("/api/messages/request/{$serviceRequest->id}")
            ->assertOk()
            ->assertJsonPath('messages.0.message', 'Please check the network outlet in our office.');
    }

    private function makeRequest($owner): ServiceRequest
    {
        return ServiceRequest::create([
            'control_number' => 'TEST-CHAT-' . Str::upper(Str::random(8)),
            'user_id' => $owner->id,
            'requester_name' => $owner->full_name,
            'requester_office' => $owner->office,
            'office' => $owner->office,
            'services' => ['troubleshoot_pc'],
            'description' => 'Conversation discovery test request.',
            'status' => 'pending',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
        ]);
    }
}
