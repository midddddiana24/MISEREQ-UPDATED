<?php

namespace Tests\Unit;

use App\Models\ClarificationAttachment;
use App\Models\IctIncidentReport;
use App\Models\Message;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestClarification;
use App\Models\User;
use App\Policies\AdminServiceRequestPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    public function test_user_policy_allows_owner_and_admin_cases_and_denies_unauthorized_cases(): void
    {
        $admin = $this->user(1, true);
        $owner = $this->user(2);
        $other = $this->user(3);
        $policy = new UserPolicy;
        Gate::define('viewSession', fn(User $user, int $targetId): bool => $policy->viewSession($user, $targetId));
        Gate::define('revokeSession', fn(User $user, int $targetId): bool => $policy->revokeSession($user, $targetId));
        Gate::define('verifyDevice', fn(User $user, int $targetId): bool => $policy->verifyDevice($user, $targetId));
        Gate::define('accessDeviceChallenge', fn(User $user, int $targetId): bool => $policy->accessDeviceChallenge($user, $targetId));

        $this->assertTrue($this->allows($admin, 'viewAny', User::class));
        $this->assertFalse($this->allows($owner, 'viewAny', User::class));
        $this->assertTrue($this->allows($owner, 'view', $owner));
        $this->assertTrue($this->allows($admin, 'view', $owner));
        $this->assertFalse($this->allows($other, 'view', $owner));
        $this->assertTrue($this->allows($admin, 'create', User::class));
        $this->assertFalse($this->allows($owner, 'create', User::class));
        $this->assertTrue($this->allows($owner, 'update', $owner));
        $this->assertTrue($this->allows($admin, 'update', $owner));
        $this->assertFalse($this->allows($other, 'update', $owner));
        $this->assertTrue($this->allows($admin, 'delete', $owner));
        $this->assertFalse($this->allows($admin, 'delete', $admin));
        $this->assertFalse($this->allows($owner, 'delete', $other));
        $this->assertTrue($this->allows($owner, 'viewSession', $owner->id));
        $this->assertTrue($this->allows($admin, 'viewSession', $owner->id));
        $this->assertFalse($this->allows($other, 'viewSession', $owner->id));
        $this->assertTrue($this->allows($owner, 'revokeSession', $owner->id));
        $this->assertTrue($this->allows($admin, 'revokeSession', $owner->id));
        $this->assertFalse($this->allows($other, 'revokeSession', $owner->id));
        $this->assertTrue($this->allows($admin, 'verifyDevice', $admin->id));
        $this->assertFalse($this->allows($owner, 'verifyDevice', $owner->id));
        $this->assertFalse($this->allows($admin, 'verifyDevice', $owner->id));
        $this->assertTrue($this->allows($admin, 'accessDeviceChallenge', $admin->id));
        $this->assertFalse($this->allows($owner, 'accessDeviceChallenge', $owner->id));
        $this->assertFalse($this->allows($admin, 'accessDeviceChallenge', $owner->id));
    }

    public function test_service_request_policy_covers_owner_admin_locked_and_status_branches(): void
    {
        $admin = $this->user(1, true);
        $owner = $this->user(2);
        $other = $this->user(3);
        $request = new ServiceRequest(['user_id' => $owner->id, 'status' => 'pending', 'attachment' => 'file.pdf', 'scheduled_visit_at' => now()]);
        $feedbackRequest = new ServiceRequest(['user_id' => $owner->id, 'status' => 'awaiting_confirmation', 'attachment' => 'file.pdf', 'scheduled_visit_at' => now()]);
        $closed = new ServiceRequest(['user_id' => $owner->id, 'status' => 'closed']);
        $locked = $this->user(4, false, ['locked_until' => now()->addHour()]);

        $this->assertTrue($this->allows($owner, 'viewAny', ServiceRequest::class));
        $this->assertTrue($this->allows($other, 'viewAny', ServiceRequest::class));
        $this->assertTrue($this->allows($owner, 'view', $request));
        $this->assertTrue($this->allows($admin, 'view', $request));
        $this->assertFalse($this->allows($other, 'view', $request));
        $this->assertTrue($this->allows($owner, 'create', ServiceRequest::class));
        $this->assertTrue($this->allows($admin, 'create', ServiceRequest::class));
        $this->assertFalse($this->allows($locked, 'create', ServiceRequest::class));
        $this->assertTrue($this->allows($owner, 'update', $request));
        $this->assertFalse($this->allows($admin, 'update', $request));
        $this->assertFalse($this->allows($other, 'update', $request));
        $this->assertTrue($this->allows($owner, 'edit', $request));
        $this->assertFalse($this->allows($owner, 'edit', $closed));
        $this->assertTrue($this->allows($owner, 'cancel', $request));
        $this->assertTrue($this->allows($owner, 'reopen', $closed));
        $this->assertTrue($this->allows($owner, 'answerClarification', $request));
        $this->assertTrue($this->allows($owner, 'submitFeedback', $feedbackRequest));
        $this->assertFalse($this->allows($owner, 'submitFeedback', new ServiceRequest(['user_id' => $owner->id, 'status' => 'processing'])));
        $this->assertTrue($this->allows($owner, 'confirmWorkCompletion', $feedbackRequest));
        $this->assertFalse($this->allows($owner, 'confirmWorkCompletion', $closed));
        $this->assertTrue($this->allows($owner, 'respondToSchedule', $request));
        $this->assertFalse($this->allows($owner, 'respondToSchedule', new ServiceRequest(['user_id' => $owner->id])));
        $this->assertTrue($this->allows($owner, 'downloadAttachment', $request));
        $this->assertFalse($this->allows($other, 'downloadAttachment', $request));
        $this->assertTrue($this->allows($owner, 'downloadUploadedAttachment', $request));
        $this->assertFalse($this->allows($other, 'downloadUploadedAttachment', $request));
        $this->assertTrue($this->allows($owner, 'destroyUploadedAttachment', $request));
        $this->assertFalse($this->allows($other, 'destroyUploadedAttachment', $request));
        $this->assertTrue($this->allows($owner, 'viewScheduledVisit', $request));
        $this->assertFalse($this->allows($other, 'viewScheduledVisit', $request));
        $this->assertTrue($this->allows($owner, 'accessClarificationAttachment', $request));
        $this->assertFalse($this->allows($other, 'accessClarificationAttachment', $request));
        $this->assertTrue($this->allows($owner, 'accessAttachment', $request));
        $this->assertFalse($this->allows($other, 'accessAttachment', $request));
        $this->assertTrue($this->allows($admin, 'delete', $request));
        $this->assertFalse($this->allows($owner, 'delete', $request));
        $this->assertTrue($this->allows($admin, 'restore', $request));
        $this->assertFalse($this->allows($owner, 'restore', $request));
        $this->assertTrue($this->allows($admin, 'forceDelete', $request));
        $this->assertFalse($this->allows($owner, 'forceDelete', $request));
    }

    public function test_checklist_incident_message_and_attachment_policies_cover_allow_and_deny_cases(): void
    {
        $admin = $this->user(1, true);
        $owner = $this->user(2);
        $other = $this->user(3);
        $checklist = new PreventiveMaintenanceChecklist(['user_id' => $owner->id]);
        $report = new IctIncidentReport(['user_id' => $owner->id, 'status' => 'open']);
        $closedReport = new IctIncidentReport(['user_id' => $owner->id, 'status' => 'closed']);
        $request = new ServiceRequest(['user_id' => $owner->id]);
        $message = new Message(['sender_id' => $owner->id]);
        $message->setRelation('serviceRequest', $request);
        $orphanMessage = new Message(['sender_id' => $owner->id]);
        $attachment = new ClarificationAttachment(['uploaded_by' => $owner->id]);
        $clarification = new ServiceRequestClarification;
        $clarification->setRelation('serviceRequest', $request);
        $attachment->setRelation('clarification', $clarification);
        $locked = $this->user(4, false, ['locked_until' => now()->addHour()]);

        foreach ([PreventiveMaintenanceChecklist::class, IctIncidentReport::class] as $model) {
            $this->assertTrue($this->allows($owner, 'viewAny', $model));
            $this->assertTrue($this->allows($other, 'viewAny', $model));
        }
        $this->assertTrue($this->allows($owner, 'view', $checklist));
        $this->assertTrue($this->allows($admin, 'view', $checklist));
        $this->assertFalse($this->allows($other, 'view', $checklist));
        $this->assertTrue($this->allows($owner, 'create', PreventiveMaintenanceChecklist::class));
        $this->assertTrue($this->allows($admin, 'create', PreventiveMaintenanceChecklist::class));
        $this->assertFalse($this->allows($locked, 'create', PreventiveMaintenanceChecklist::class));
        $this->assertTrue($this->allows($owner, 'update', $checklist));
        $this->assertTrue($this->allows($admin, 'update', $checklist));
        $this->assertFalse($this->allows($other, 'update', $checklist));
        foreach (['delete', 'restore', 'forceDelete'] as $ability) {
            $this->assertTrue($this->allows($admin, $ability, $checklist));
            $this->assertFalse($this->allows($owner, $ability, $checklist));
        }

        $this->assertTrue($this->allows($owner, 'view', $report));
        $this->assertTrue($this->allows($admin, 'view', $report));
        $this->assertFalse($this->allows($other, 'view', $report));
        $this->assertTrue($this->allows($owner, 'create', IctIncidentReport::class));
        $this->assertTrue($this->allows($admin, 'create', IctIncidentReport::class));
        $this->assertFalse($this->allows($locked, 'create', IctIncidentReport::class));
        $this->assertTrue($this->allows($owner, 'update', $report));
        $this->assertTrue($this->allows($admin, 'update', $report));
        $this->assertFalse($this->allows($other, 'update', $report));
        $this->assertTrue($this->allows($owner, 'edit', $report));
        $this->assertFalse($this->allows($owner, 'edit', $closedReport));
        foreach (['delete', 'restore', 'forceDelete'] as $ability) {
            $this->assertTrue($this->allows($admin, $ability, $report));
            $this->assertFalse($this->allows($owner, $ability, $report));
        }

        $this->assertTrue($this->allows($owner, 'viewAny', Message::class));
        $this->assertTrue($this->allows($other, 'viewAny', Message::class));
        $this->assertTrue($this->allows($owner, 'view', $message));
        $this->assertTrue($this->allows($admin, 'view', $message));
        $this->assertFalse($this->allows($other, 'view', $message));
        $this->assertFalse($this->allows($other, 'view', $orphanMessage));
        $this->assertTrue($this->allows($owner, 'downloadAttachment', $message));
        $this->assertFalse($this->allows($other, 'downloadAttachment', $message));
        $this->assertTrue($this->allows($owner, 'create', Message::class));
        $this->assertFalse($this->allows($locked, 'create', Message::class));
        $this->assertTrue($this->allows($owner, 'delete', $message));
        $this->assertTrue($this->allows($admin, 'delete', $message));
        $this->assertFalse($this->allows($other, 'delete', $message));

        $this->assertTrue($this->allows($owner, 'view', $attachment));
        $this->assertTrue($this->allows($admin, 'view', $attachment));
        $this->assertFalse($this->allows($other, 'view', $attachment));
        $this->assertTrue($this->allows($owner, 'download', $attachment));
        $this->assertFalse($this->allows($other, 'download', $attachment));
        $this->assertTrue($this->allows($owner, 'delete', $attachment));
        $this->assertTrue($this->allows($admin, 'delete', $attachment));
        $this->assertFalse($this->allows($other, 'delete', $attachment));
    }

    public function test_admin_service_request_policy_allows_only_admins_for_every_ability(): void
    {
        $policy = new AdminServiceRequestPolicy;
        $admin = $this->user(1, true);
        $user = $this->user(2);
        $request = new ServiceRequest;

        foreach (['viewAny', 'view', 'assignTechnician', 'scheduleVisit', 'requestClarification', 'reviewFeedback', 'generateReport', 'updateStatus', 'delete', 'restore', 'forceDelete'] as $ability) {
            $arguments = $ability === 'viewAny' ? [$admin] : [$admin, $request];
            $denialArguments = $ability === 'viewAny' ? [$user] : [$user, $request];
            $this->assertTrue($policy->{$ability}(...$arguments), $ability . ' should allow admins');
            $this->assertFalse($policy->{$ability}(...$denialArguments), $ability . ' should deny regular users');
        }
    }

    private function user(int $id, bool $admin = false, array $attributes = []): User
    {
        $user = new User(array_merge([
            'role' => $admin ? 'admin' : 'user',
            'is_admin' => $admin,
            'is_active' => true,
        ], $attributes));
        $user->id = $id;

        return $user;
    }

    private function allows(User $user, string $ability, mixed ...$arguments): bool
    {
        return Gate::forUser($user)->allows($ability, $arguments);
    }
}
