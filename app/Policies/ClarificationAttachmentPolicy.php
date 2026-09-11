<?php

namespace App\Policies;

use App\Models\ClarificationAttachment;
use App\Models\User;

class ClarificationAttachmentPolicy
{
    /**
     * Determine if the user can view the attachment.
     */
    public function view(User $user, ClarificationAttachment $attachment): bool
    {
        $clarification = $attachment->clarification;
        if (! $clarification) {
            return false;
        }

        $request = $clarification->serviceRequest;
        if (! $request) {
            return false;
        }

        // Owner, uploader, or admin can view
        return $user->id === $request->user_id ||
            $user->id === $attachment->uploaded_by ||
            $user->isAdmin();
    }

    /**
     * Determine if the user can download the attachment.
     */
    public function download(User $user, ClarificationAttachment $attachment): bool
    {
        return $this->view($user, $attachment);
    }

    /**
     * Determine if the user can delete the attachment.
     */
    public function delete(User $user, ClarificationAttachment $attachment): bool
    {
        return $user->id === $attachment->uploaded_by || $user->isAdmin();
    }
}
