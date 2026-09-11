<?php

namespace App\Policies;

use App\Models\PreventiveMaintenanceChecklist;
use App\Models\User;

class PreventiveMaintenanceChecklistPolicy
{
    /**
     * Determine if the user can view any checklist.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the checklist.
     */
    public function view(User $user, PreventiveMaintenanceChecklist $checklist): bool
    {
        return $user->id === $checklist->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can create a checklist.
     */
    public function create(User $user): bool
    {
        return ($user->role === 'user' || $user->isAdmin())
            && ! $user->isAccountLocked();
    }

    /**
     * Determine if the user can update the checklist.
     */
    public function update(User $user, PreventiveMaintenanceChecklist $checklist): bool
    {
        return $user->id === $checklist->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the checklist.
     */
    public function delete(User $user, PreventiveMaintenanceChecklist $checklist): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can restore the checklist.
     */
    public function restore(User $user, PreventiveMaintenanceChecklist $checklist): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the checklist.
     */
    public function forceDelete(User $user, PreventiveMaintenanceChecklist $checklist): bool
    {
        return $user->isAdmin();
    }
}
