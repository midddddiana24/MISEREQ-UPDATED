<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IctIncidentReport;

class IctIncidentReportPolicy
{
    /**
     * Determine if the user can view any incident report.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the incident report.
     */
    public function view(User $user, IctIncidentReport $report): bool
    {
        return $user->id === $report->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can create an incident report.
     */
    public function create(User $user): bool
    {
        return ($user->role === 'user' || $user->isAdmin())
            && !$user->isAccountLocked();
    }

    /**
     * Determine if the user can update the incident report.
     */
    public function update(User $user, IctIncidentReport $report): bool
    {
        return $user->id === $report->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can edit the incident report (status check included).
     */
    public function edit(User $user, IctIncidentReport $report): bool
    {
        // Cannot edit completed incident reports
        if (in_array($report->status, ['resolved', 'closed'], true)) {
            return false;
        }

        return $this->update($user, $report);
    }

    /**
     * Determine if the user can delete the incident report.
     */
    public function delete(User $user, IctIncidentReport $report): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can restore the incident report.
     */
    public function restore(User $user, IctIncidentReport $report): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the incident report.
     */
    public function forceDelete(User $user, IctIncidentReport $report): bool
    {
        return $user->isAdmin();
    }
}
