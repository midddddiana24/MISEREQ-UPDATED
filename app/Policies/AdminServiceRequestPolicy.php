<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class AdminServiceRequestPolicy
{
    /**
     * Determine if admin can view any service request (admin dashboard list).
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can view a specific service request details.
     */
    public function view(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can assign a technician to this request.
     */
    public function assignTechnician(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can schedule a visit for this request.
     */
    public function scheduleVisit(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can request clarification from user.
     */
    public function requestClarification(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can review/approve feedback.
     */
    public function reviewFeedback(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can generate PDF report.
     */
    public function generateReport(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can update/transition service request status.
     */
    public function updateStatus(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can archive/delete a service request.
     */
    public function delete(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can restore an archived request.
     */
    public function restore(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if admin can permanently delete a request.
     */
    public function forceDelete(User $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }
}
