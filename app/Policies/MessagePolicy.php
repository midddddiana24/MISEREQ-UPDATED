<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine if the user can view messages for a service request.
     */
    public function viewAny(User $user, ?int $serviceRequestId = null): bool
    {
        if ($serviceRequestId === null) {
            return true; // Can view messages if authorized for specific request
        }

        return true; // Authorization happens per-request
    }

    /**
     * Determine if the user can view the message.
     */
    public function view(User $user, Message $message): bool
    {
        $serviceRequest = $message->serviceRequest;
        if (! $serviceRequest) {
            return false;
        }

        // Message owner, request owner, or admin can view
        return $user->id === $message->sender_id ||
            $user->id === $serviceRequest->user_id ||
            $user->isAdmin();
    }

    /**
     * Determine if the user can download a message attachment.
     */
    public function downloadAttachment(User $user, Message $message): bool
    {
        return $this->view($user, $message);
    }

    /**
     * Determine if the user can create a message.
     */
    public function create(User $user): bool
    {
        return ! $user->isAccountLocked();
    }

    /**
     * Determine if the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id || $user->isAdmin();
    }
}
