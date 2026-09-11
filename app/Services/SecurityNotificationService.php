<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SecurityNotificationService
{
    public function notify(
        User $user,
        string $subject,
        string $message,
        ?string $actionUrl = null,
        ?string $emailMessage = null,
        bool $forceEmail = false
    ): Notification {
        $notification = Notification::create([
            'service_request_id' => null,
            'user_id' => $user->id,
            'recipient_email' => $user->email,
            'notification_type' => 'security_alert',
            'subject' => $subject,
            'message' => $message,
            'action_url' => $actionUrl ?? ($user->isAdmin() ? route('admin.settings') : route('user.settings')),
            'status' => 'pending',
        ]);

        $emailAllowed = $forceEmail || (
            Setting::get('security_login_alerts', true)
            && $user->notify_email_enabled
            && $user->notify_security_alerts
        );

        if (!$emailAllowed) {
            $notification->markAsSent('Security alert retained in app; email preference is disabled.');
            return $notification;
        }

        try {
            $mailNotification = clone $notification;
            if ($emailMessage !== null) {
                $mailNotification->message = $emailMessage;
            }

            Mail::send('emails.generic-notification', [
                'notification' => $mailNotification,
            ], function ($mail) use ($notification) {
                $mail->to($notification->recipient_email)
                    ->subject($notification->subject)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(
                        config('mail.from.address', 'noreply@example.com'),
                        config('mail.from.name', 'MIS Service Request')
                    );
            });
            $notification->markAsSent('Security email sent successfully.');
        } catch (Throwable $exception) {
            $notification->markAsFailed($exception->getMessage());
        }

        return $notification;
    }
}
