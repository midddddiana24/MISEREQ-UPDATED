<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\ClarificationAttachment;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestAttachment;
use App\Models\ServiceRequestClarification;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RemainingBlindUserRoutesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('secure');
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);
    }

    public function test_owner_can_answer_their_requests_clarification(): void
    {
        $owner = $this->createUser();
        $request = $this->makeRequest($owner, [
            'status' => 'awaiting_requester_info',
            'status_before_clarification' => 'pending',
        ]);
        $clarification = $this->makeClarification($request);

        $this->actingAs($owner)
            ->post(route('requests.clarifications.answer', [$request, $clarification]), [
                'answer' => 'The affected computer is in Room 204 and may be inspected today.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $clarification->refresh();
        $this->assertSame($request->id, $clarification->service_request_id);
        $this->assertSame($owner->id, $clarification->answered_by);
        $this->assertSame('The affected computer is in Room 204 and may be inspected today.', $clarification->answer);
        $this->assertNotNull($clarification->answered_at);
        $this->assertSame('pending', $request->fresh()->status);
    }

    public function test_clarification_answer_rejects_empty_answers(): void
    {
        $owner = $this->createUser();
        $request = $this->makeRequest($owner, [
            'status' => 'awaiting_requester_info',
            'status_before_clarification' => 'pending',
        ]);
        $clarification = $this->makeClarification($request);

        $this->actingAs($owner)
            ->post(route('requests.clarifications.answer', [$request, $clarification]), [])
            ->assertSessionHasErrors('answer');

        $this->assertNull($clarification->fresh()->answer);
        $this->assertNull($clarification->fresh()->answered_at);
        $this->assertSame('awaiting_requester_info', $request->fresh()->status);
    }

    public function test_clarification_answer_denies_non_owner_and_mismatched_parent(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $request = $this->makeRequest($owner, ['status' => 'awaiting_requester_info']);
        $clarification = $this->makeClarification($request);

        $this->actingAs($other)
            ->post(route('requests.clarifications.answer', [$request, $clarification]), ['answer' => 'Unauthorized'])
            ->assertForbidden();

        $otherRequest = $this->makeRequest($owner, ['status' => 'awaiting_requester_info']);
        $this->actingAs($owner)
            ->post(route('requests.clarifications.answer', [$otherRequest, $clarification]), ['answer' => 'Wrong parent'])
            ->assertNotFound();

        $this->assertNull($clarification->fresh()->answer);
    }

    public function test_owner_can_accept_a_scheduled_visit(): void
    {
        $owner = $this->createUser();
        $request = $this->makeRequest($owner, [
            'scheduled_visit_at' => now()->addDay(),
            'scheduled_location' => 'MIS Office',
        ]);

        $this->actingAs($owner)
            ->post(route('requests.schedule-response', $request), [
                'schedule_response' => 'accepted',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $request->refresh();
        $this->assertSame('accepted', $request->schedule_response);
        $this->assertNull($request->schedule_response_note);
        $this->assertNotNull($request->schedule_responded_at);
        $this->assertDatabaseHas('request_status_logs', [
            'service_request_id' => $request->id,
            'updated_by' => $owner->id,
            'remarks' => 'Requester accepted the scheduled MIS visit.',
        ]);
    }

    public function test_schedule_response_is_owner_only_including_against_administrators(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $request = $this->makeRequest($owner, ['scheduled_visit_at' => now()->addDay()]);

        foreach ([$other, $admin] as $actor) {
            $this->actingAs($actor)
                ->post(route('requests.schedule-response', $request), [
                    'schedule_response' => 'accepted',
                ])
                ->assertForbidden();
        }

        $this->assertNull($request->fresh()->schedule_response);
    }

    public function test_user_can_mark_only_their_notification_as_read(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $owned = $this->makeNotification($owner);
        $foreign = $this->makeNotification($other);

        $this->actingAs($owner)
            ->postJson(route('notification.read', $owned->id))
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertNotNull($owned->fresh()->read_at);

        $this->postJson(route('notification.read', $foreign->id))->assertNotFound();
        $this->assertNull($foreign->fresh()->read_at);
    }

    public function test_user_can_delete_only_their_notification(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $owned = $this->makeNotification($owner);
        $foreign = $this->makeNotification($other);

        $this->actingAs($owner)
            ->deleteJson(route('notification.delete', $owned->id))
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertDatabaseMissing('notifications', ['id' => $owned->id]);

        $this->deleteJson(route('notification.delete', $foreign->id))->assertNotFound();
        $this->assertDatabaseHas('notifications', ['id' => $foreign->id, 'user_id' => $other->id]);
    }

    public function test_owner_can_delete_an_editable_requests_attachment_and_physical_file(): void
    {
        $owner = $this->createUser();
        $request = $this->makeRequest($owner);
        $path = 'request-attachments/'.Str::uuid().'.pdf';
        Storage::disk('secure')->put($path, 'attachment contents');
        $request->update(['attachment' => $path]);
        $attachment = $this->makeRequestAttachment($request, $owner, $path);

        $this->actingAs($owner)
            ->delete(route('requests.attachments.destroy', [$request, $attachment]))
            ->assertRedirect()
            ->assertSessionHas('success');

        Storage::disk('secure')->assertMissing($path);
        $this->assertDatabaseMissing('service_request_attachments', ['id' => $attachment->id]);
        $this->assertNull($request->fresh()->attachment);
    }

    public function test_attachment_deletion_denies_non_owner_and_administrator(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $request = $this->makeRequest($owner);
        $path = 'request-attachments/'.Str::uuid().'.pdf';
        Storage::disk('secure')->put($path, 'attachment contents');
        $attachment = $this->makeRequestAttachment($request, $owner, $path);

        foreach ([$other, $admin] as $actor) {
            $this->actingAs($actor)
                ->delete(route('requests.attachments.destroy', [$request, $attachment]))
                ->assertForbidden();
        }

        Storage::disk('secure')->assertExists($path);
        $this->assertDatabaseHas('service_request_attachments', ['id' => $attachment->id]);
    }

    public function test_owner_and_administrator_can_download_a_matching_clarification_attachment(): void
    {
        $owner = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);
        $request = $this->makeRequest($owner);
        $clarification = $this->makeClarification($request);
        $attachment = $this->makeClarificationAttachment($clarification, $owner);
        Storage::disk('secure')->put($attachment->path, 'clarification attachment contents');

        foreach ([$owner, $admin] as $actor) {
            $this->actingAs($actor)
                ->get(route('requests.clarification-attachments.download', [$request, $attachment]))
                ->assertOk()
                ->assertDownload($attachment->original_name);
        }
    }

    public function test_clarification_attachment_download_denies_non_owner_and_mismatched_parent(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $request = $this->makeRequest($owner);
        $clarification = $this->makeClarification($request);
        $attachment = $this->makeClarificationAttachment($clarification, $owner);
        Storage::disk('secure')->put($attachment->path, 'clarification attachment contents');

        $this->actingAs($other)
            ->get(route('requests.clarification-attachments.download', [$request, $attachment]))
            ->assertForbidden();

        $otherRequest = $this->makeRequest($owner);
        $this->actingAs($owner)
            ->get(route('requests.clarification-attachments.download', [$otherRequest, $attachment]))
            ->assertNotFound();
    }

    #[DataProvider('guestRouteProvider')]
    public function test_remaining_routes_reject_guests(string $method, string $routeName, string $fixture): void
    {
        $owner = $this->createUser();
        $request = $this->makeRequest($owner, [
            'status' => $fixture === 'clarification' ? 'awaiting_requester_info' : 'pending',
            'scheduled_visit_at' => $fixture === 'schedule' ? now()->addDay() : null,
        ]);

        $parameters = match ($fixture) {
            'clarification' => [$request, $this->makeClarification($request)],
            'schedule' => [$request],
            'notification' => [$this->makeNotification($owner)->id],
            'attachment' => [$request, $this->makeRequestAttachment($request, $owner)],
            'clarification_attachment' => [
                $request,
                $this->makeClarificationAttachment($this->makeClarification($request), $owner),
            ],
        };

        $this->call($method, route($routeName, $parameters))->assertRedirect(route('login'));
    }

    public static function guestRouteProvider(): array
    {
        return [
            'answer clarification' => ['POST', 'requests.clarifications.answer', 'clarification'],
            'schedule response' => ['POST', 'requests.schedule-response', 'schedule'],
            'read notification' => ['POST', 'notification.read', 'notification'],
            'delete notification' => ['DELETE', 'notification.delete', 'notification'],
            'delete request attachment' => ['DELETE', 'requests.attachments.destroy', 'attachment'],
            'download clarification attachment' => ['GET', 'requests.clarification-attachments.download', 'clarification_attachment'],
        ];
    }

    private function makeRequest(User $owner, array $attributes = []): ServiceRequest
    {
        return ServiceRequest::create(array_merge([
            'control_number' => 'BLIND-'.Str::upper(Str::random(10)),
            'user_id' => $owner->id,
            'requester_name' => $owner->full_name,
            'requester_office' => $owner->office,
            'office' => $owner->office,
            'services' => ['troubleshoot_pc'],
            'description' => 'Remaining blind route feature test.',
            'status' => 'pending',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
        ], $attributes));
    }

    private function makeClarification(ServiceRequest $request): ServiceRequestClarification
    {
        return ServiceRequestClarification::create([
            'service_request_id' => $request->id,
            'question' => 'Please provide the device location.',
        ]);
    }

    private function makeNotification(User $user): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'recipient_email' => $user->email,
            'notification_type' => 'status_update',
            'subject' => 'Request update',
            'message' => 'Your request has an update.',
            'status' => 'sent',
        ]);
    }

    private function makeRequestAttachment(
        ServiceRequest $request,
        User $uploader,
        ?string $path = null
    ): ServiceRequestAttachment {
        return ServiceRequestAttachment::create([
            'service_request_id' => $request->id,
            'uploaded_by' => $uploader->id,
            'path' => $path ?? 'request-attachments/'.Str::uuid().'.pdf',
            'original_name' => 'request-document.pdf',
            'mime_type' => 'application/pdf',
            'size' => 19,
        ]);
    }

    private function makeClarificationAttachment(
        ServiceRequestClarification $clarification,
        User $uploader
    ): ClarificationAttachment {
        return ClarificationAttachment::create([
            'service_request_clarification_id' => $clarification->id,
            'uploaded_by' => $uploader->id,
            'path' => 'clarification-attachments/'.Str::uuid().'.pdf',
            'original_name' => 'clarification.pdf',
            'mime_type' => 'application/pdf',
            'size' => 33,
        ]);
    }
}
