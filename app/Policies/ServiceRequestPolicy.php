<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    /**
     * Determine if the user can view any service request.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view the list
    }

    /**
     * Determine if the user can view the service request.
     */
    public function view(User $user, ServiceRequest $request): bool
    {
        // Own request or admin
        return $user->id === $request->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can create a service request.
     */
    public function create(User $user): bool
    {
        return ($user->role === 'user' || $user->isAdmin())
            && ! $user->isAccountLocked();
    }

    /**
     * Determine if the user can update the service request.
     * Only owner can update, admins cannot update user requests.
     */
    public function update(User $user, ServiceRequest $request): bool
    {
        return $user->id === $request->user_id;
    }

    /**
     * Determine if the user can edit the service request (includes status checks).
     */
    public function edit(User $user, ServiceRequest $request): bool
    {
        return $this->update($user, $request) && $request->canBeEditedByUser();
    }

    /**
     * Determine if the user can cancel the service request.
     */
    public function cancel(User $user, ServiceRequest $request): bool
    {
        return $this->update($user, $request) && $request->canBeCancelledByUser();
    }

    /**
     * Determine if the user can reopen the service request.
     */
    public function reopen(User $user, ServiceRequest $request): bool
    {
        return $this->update($user, $request) && $request->canBeReopenedByUser();
    }

    /**
     * Determine if the user can answer a clarification.
     */
    public function answerClarification(User $user, ServiceRequest $request): bool
    {
        return $this->update($user, $request);
    }

    /**
     * Determine if the user can submit feedback on the completed work.
     */
    public function submitFeedback(User $user, ServiceRequest $request): bool
    {
        return $user->id === $request->user_id &&
            in_array($request->status, ['awaiting_confirmation', 'completed', 'closed'], true);
    }

    /**
     * Determine if the user can confirm work completion feedback.
     */
    public function confirmWorkCompletion(User $user, ServiceRequest $request): bool
    {
        return $user->id === $request->user_id &&
            in_array($request->status, ['awaiting_confirmation', 'completed'], true);
    }

    /**
     * Determine if the user can respond to a scheduled visit.
     */
    public function respondToSchedule(User $user, ServiceRequest $request): bool
    {
        return $user->id === $request->user_id && $request->scheduled_visit_at;
    }

    /**
     * Determine if the user can download attachments from this request.
     */
    public function downloadAttachment(User $user, ServiceRequest $request): bool
    {
        return $this->view($user, $request) && $request->attachment;
    }

    /**
     * Determine if the user can download uploaded attachments.
     */
    public function downloadUploadedAttachment(User $user, ServiceRequest $request): bool
    {
        return $this->view($user, $request);
    }

    /**
     * Determine if the user can destroy uploaded attachments.
     */
    public function destroyUploadedAttachment(User $user, ServiceRequest $request): bool
    {
        return $this->edit($user, $request);
    }

    /**
     * Determine if the user can view scheduled visit details.
     */
    public function viewScheduledVisit(User $user, ServiceRequest $request): bool
    {
        return $this->view($user, $request) && $request->scheduled_visit_at;
    }

    /**
     * Determine if the user can access clarification attachment.
     */
    public function accessClarificationAttachment(User $user, ServiceRequest $request): bool
    {
        return $this->view($user, $request);
    }

    /**
     * Determine if the user can access service request attachment.
     */
    public function accessAttachment(User $user, ServiceRequest $request): bool
    {
        return $this->view($user, $request);
    }

    /**
     * Determine if the user can delete the service request.
     */
    public function delete(User $user, ServiceRequest $request): bool
    {
        // Only admin can delete
        return $user->isAdmin();
    }

    /**
     * Determine if the user can restore a deleted request.
     */
    public function restore(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the request.
     */
    public function forceDelete(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }
}
