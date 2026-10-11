<?php

namespace App\Services;

use App\Mail\SecurityAlertMail;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Throwable;

class SecurityNotificationService
{
    public const EVENT_PASSWORD_CHANGED = 'password_changed';

    public const EVENT_TWO_FACTOR_DISABLED = 'two_factor_disabled';

    public const EVENT_NEW_DEVICE = 'new_device';

    public const EVENT_EMAIL_CHANGED = 'email_changed';

    private const MANDATORY_EMAIL_EVENTS = [
        self::EVENT_PASSWORD_CHANGED,
        self::EVENT_TWO_FACTOR_DISABLED,
        self::EVENT_NEW_DEVICE,
        self::EVENT_EMAIL_CHANGED,
    ];

    public function notify(
        User $user,
        string $subject,
        string $message,
        ?string $actionUrl = null,
        ?string $emailMessage = null
    ): Notification {
        return $this->deliver($user, $subject, $message, $actionUrl, $emailMessage, false);
    }

    public function notifyMandatory(
        string $event,
        User $user,
        string $subject,
        string $message,
        ?string $actionUrl = null,
        ?string $emailMessage = null,
        ?string $recipientEmail = null
    ): Notification {
        if (! in_array($event, self::MANDATORY_EMAIL_EVENTS, true)) {
            throw new InvalidArgumentException("{$event} is not a mandatory security email event.");
        }

        return $this->deliver($user, $subject, $message, $actionUrl, $emailMessage, true, $recipientEmail);
    }

    private function deliver(
        User $user,
        string $subject,
        string $message,
        ?string $actionUrl,
        ?string $emailMessage,
        bool $mandatory,
        ?string $recipientEmail = null
    ): Notification {
        $notification = Notification::create([
            'service_request_id' => null,
            'user_id' => $user->id,
            'recipient_email' => $recipientEmail ?? $user->email,
            'notification_type' => 'security_alert',
            'subject' => $subject,
            'message' => $message,
            'action_url' => $actionUrl ?? ($user->isAdmin() ? route('admin.settings') : route('user.settings')),
            'status' => 'pending',
        ]);

        $emailAllowed = $mandatory || (
            Setting::get('security_login_alerts', true)
            && $user->notify_email_enabled
            && $user->notify_security_alerts
        );

        if (! $emailAllowed) {
            $notification->markAsSent('Security alert retained in app; email preference is disabled.');

            return $notification;
        }

        try {
            $mailNotification = clone $notification;
            if ($emailMessage !== null) {
                $mailNotification->message = $emailMessage;
            }

            Mail::to($notification->recipient_email)->send(new SecurityAlertMail($mailNotification));
            $notification->markAsSent($mandatory
                ? 'Mandatory security email sent successfully.'
                : 'Security email sent successfully.');
        } catch (Throwable $exception) {
            $notification->markAsFailed($exception->getMessage());
        }

        return $notification;
    }
}
