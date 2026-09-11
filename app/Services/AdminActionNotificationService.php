<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;

class AdminActionNotificationService
{
    public function notify(
        string $subject,
        string $message,
        string $actionUrl,
        ?int $serviceRequestId = null
    ): void {
        if (! Setting::get('notify_admin_alerts', true)) {
            return;
        }

        User::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('role', 'admin')->orWhere('is_admin', true);
            })
            ->get()
            ->each(function (User $admin) use ($subject, $message, $actionUrl, $serviceRequestId) {
                Notification::create([
                    'service_request_id' => $serviceRequestId,
                    'user_id' => $admin->id,
                    'recipient_email' => $admin->email,
                    'notification_type' => 'admin_action_required',
                    'subject' => $subject,
                    'message' => $message,
                    'action_url' => $actionUrl,
                    'status' => 'pending',
                ]);
            });
    }
}
