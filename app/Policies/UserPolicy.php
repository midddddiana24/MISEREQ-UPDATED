<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any user.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view the user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine if the user can create a user.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine if the user can view their session.
     */
    public function viewSession(User $user, int $sessionUserId): bool
    {
        return $user->id === $sessionUserId || $user->isAdmin();
    }

    /**
     * Determine if the user can revoke a session.
     */
    public function revokeSession(User $user, int $sessionUserId): bool
    {
        return $user->id === $sessionUserId || $user->isAdmin();
    }

    /**
     * Determine if the user can verify a device (2FA).
     */
    public function verifyDevice(User $user, int $targetUserId): bool
    {
        return $user->id === $targetUserId && $user->isAdmin();
    }

    /**
     * Determine if the user can access device verification challenge.
     */
    public function accessDeviceChallenge(User $user, int $targetUserId): bool
    {
        return $user->id === $targetUserId && $user->isAdmin();
    }
}
