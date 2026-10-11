<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessengerRoundTripTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        Storage::fake('secure');
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);
    }

    public function test_user_and_admin_can_exchange_messages_and_private_attachments(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $serviceRequest = $this->makeRequest($owner);

        $userSend = $this->actingAs($owner)->post('/api/messages/send', [
            'service_request_id' => $serviceRequest->id,
            'message' => 'Here is the network outlet photo.',
            'attachment' => UploadedFile::fake()->image('outlet.png'),
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('success', true);

        $userMessage = Message::findOrFail($userSend->json('message.id'));
        $userAttachment = MessageAttachment::findOrFail($userSend->json('message.attachments.0.id'));
        $this->assertSame($owner->id, $userMessage->sender_id);
        $this->assertFalse($userMessage->is_admin);
        Storage::disk('secure')->assertExists($userAttachment->path);
        $this->assertSame(route('messages.attachments.download', $userAttachment), $userSend->json('message.attachments.0.download_url'));

        $this->actingAs($other)
            ->getJson("/api/messages/request/{$serviceRequest->id}")->assertForbidden();
        $this->actingAs($other)
            ->get(route('messages.attachments.download', $userAttachment))->assertForbidden();

        $this->actingAs($admin)
            ->getJson("/admin/api/messages/request/{$serviceRequest->id}")
            ->assertOk()
            ->assertJsonPath('messages.0.message', 'Here is the network outlet photo.')
            ->assertJsonPath('messages.0.attachments.0.download_url', route('admin.messages.attachments.download', $userAttachment));
        $this->assertNotNull($userMessage->fresh()->read_at);
        $this->actingAs($admin)
            ->get(route('admin.messages.attachments.download', $userAttachment))->assertOk();

        $adminSend = $this->actingAs($admin)->post('/admin/api/messages/send', [
            'service_request_id' => $serviceRequest->id,
            'message' => 'Please try the attached instructions.',
            'attachment' => UploadedFile::fake()->image('instructions.png'),
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('success', true);

        $adminMessage = Message::findOrFail($adminSend->json('message.id'));
        $adminAttachment = MessageAttachment::findOrFail($adminSend->json('message.attachments.0.id'));
        $this->assertSame($admin->id, $adminMessage->sender_id);
        $this->assertTrue($adminMessage->is_admin);
        Storage::disk('secure')->assertExists($adminAttachment->path);

        $this->actingAs($owner)
            ->getJson("/api/messages/request/{$serviceRequest->id}/new?after_id={$userMessage->id}")
            ->assertOk()
            ->assertJsonPath('messages.0.message', 'Please try the attached instructions.')
            ->assertJsonPath('messages.0.attachments.0.download_url', route('messages.attachments.download', $adminAttachment));
        $this->assertNotNull($adminMessage->fresh()->read_at);
        $this->actingAs($owner)
            ->get(route('messages.attachments.download', $adminAttachment))->assertOk();
        $this->actingAs($other)
            ->get(route('messages.attachments.download', $adminAttachment))->assertForbidden();
    }

    public function test_attachment_only_messages_work_and_empty_submissions_are_rejected(): void
    {
        $owner = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $serviceRequest = $this->makeRequest($owner);

        $this->actingAs($owner)->postJson('/api/messages/send', [
            'service_request_id' => $serviceRequest->id,
            'message' => '   ',
        ])->assertUnprocessable()->assertJsonValidationErrors('message');
        $this->assertSame(0, Message::where('service_request_id', $serviceRequest->id)->count());

        $this->actingAs($owner)->post('/api/messages/send', [
            'service_request_id' => $serviceRequest->id,
            'attachment' => UploadedFile::fake()->image('photo.png'),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('message.message', 'Attachment sent')
            ->assertJsonPath('message.attachments.0.name', 'photo.png');

        $this->actingAs($admin)->post('/admin/api/messages/send', [
            'service_request_id' => $serviceRequest->id,
            'attachment' => UploadedFile::fake()->image('instructions.png'),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('message.message', 'Attachment sent')
            ->assertJsonPath('message.attachments.0.name', 'instructions.png');

        $this->assertSame(2, Message::where('service_request_id', $serviceRequest->id)->count());
    }

    public function test_attachment_with_mismatched_filename_and_content_is_rejected_without_creating_a_message(): void
    {
        $owner = $this->createUser();
        $serviceRequest = $this->makeRequest($owner);
        $png = UploadedFile::fake()->image('actual.png');
        $mismatched = new UploadedFile($png->getRealPath(), 'misnamed.jpg', null, null, true);

        $this->actingAs($owner)->post('/api/messages/send', [
            'service_request_id' => $serviceRequest->id,
            'message' => 'See attached.',
            'attachment' => $mismatched,
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attachment');

        $this->assertSame(0, Message::where('service_request_id', $serviceRequest->id)->count());
    }

    public function test_user_messenger_builds_api_urls_under_the_application_base_path(): void
    {
        $owner = $this->createUser();
        $this->makeRequest($owner);
        $root = 'http://localhost/mis-service-request/mis-service-request/public';
        URL::forceRootUrl($root);

        try {
            $this->actingAs($owner);
            $html = view('user.messenger_new', ['requests' => collect()])->render();
            $this->assertMatchesRegularExpression('/const messagesBase = .*?;/', $html);
            $this->assertMatchesRegularExpression('/const sendUrl = .*?;/', $html);
            preg_match('/const messagesBase = (.*?);/', $html, $messagesMatch);
            preg_match('/const sendUrl = (.*?);/', $html, $sendMatch);
            $this->assertSame($root.'/api/messages/request', json_decode($messagesMatch[1], true));
            $this->assertSame($root.'/api/messages/send', json_decode($sendMatch[1], true));
        } finally {
            URL::forceRootUrl(config('app.url'));
        }
    }

    public function test_message_history_has_a_cursor_for_older_messages(): void
    {
        $owner = $this->createUser();
        $serviceRequest = $this->makeRequest($owner);

        for ($number = 1; $number <= 105; $number++) {
            Message::create([
                'service_request_id' => $serviceRequest->id,
                'sender_id' => $owner->id,
                'message' => "Message {$number}",
                'is_admin' => false,
            ]);
        }

        $latest = $this->actingAs($owner)
            ->getJson("/api/messages/request/{$serviceRequest->id}?limit=100")
            ->assertOk()
            ->assertJsonPath('has_more', true)
            ->assertJsonPath('messages.0.message', 'Message 6')
            ->assertJsonPath('messages.99.message', 'Message 105');

        $older = $this->actingAs($owner)
            ->getJson("/api/messages/request/{$serviceRequest->id}?limit=100&before_id={$latest->json('oldest_id')}")
            ->assertOk()
            ->assertJsonPath('has_more', false)
            ->assertJsonPath('messages.0.message', 'Message 1')
            ->assertJsonPath('messages.4.message', 'Message 5');

        $this->assertCount(5, $older->json('messages'));
    }

    public function test_guest_and_another_user_cannot_send_to_a_request_or_download_its_attachment(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $serviceRequest = $this->makeRequest($owner);

        $sent = $this->actingAs($owner)->post('/api/messages/send', [
            'service_request_id' => $serviceRequest->id,
            'message' => 'Private file',
            'attachment' => UploadedFile::fake()->image('private.png'),
        ], ['Accept' => 'application/json'])->assertOk();
        $attachment = MessageAttachment::findOrFail($sent->json('message.attachments.0.id'));

        $this->actingAs($other)->postJson('/api/messages/send', [
            'service_request_id' => $serviceRequest->id,
            'message' => 'Unauthorized reply',
        ])->assertForbidden();
        $this->assertSame(1, Message::where('service_request_id', $serviceRequest->id)->count());

        auth()->logout();
        $this->postJson('/api/messages/send', [
            'service_request_id' => $serviceRequest->id,
            'message' => 'Guest reply',
        ])->assertUnauthorized();
        $this->getJson(route('messages.attachments.download', $attachment))->assertUnauthorized();
        $this->assertSame(1, Message::where('service_request_id', $serviceRequest->id)->count());
    }

    private function makeRequest($owner): ServiceRequest
    {
        return ServiceRequest::create([
            'control_number' => 'TEST-MSG-'.Str::upper(Str::random(8)),
            'user_id' => $owner->id,
            'requester_name' => $owner->full_name,
            'requester_office' => $owner->office,
            'office' => $owner->office,
            'services' => ['troubleshoot_pc'],
            'description' => 'Messenger round-trip test request.',
            'status' => 'pending',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
        ]);
    }
}
