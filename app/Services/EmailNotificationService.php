<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailNotificationService
{
    /**
     * Send email notification for request creation
     */
    public function sendRequestCreatedNotification(ServiceRequest $request): Notification
    {
        $notification = Notification::create([
            'service_request_id' => $request->id,
            'user_id' => $request->user_id,
            'recipient_email' => $request->user->email,
            'notification_type' => 'request_created',
            'subject' => 'Service Request Submitted - Control #'.$request->control_number,
            'message' => 'Your service request has been successfully submitted.',
            'status' => 'pending',
        ]);

        if (! Setting::get('notify_new_requests', true) || ! $this->allowsEmail($request)) {
            $notification->markAsSent('Email disabled by administrator setting; in-app notification retained.');

            return $notification;
        }

        try {
            Mail::send('emails.request-created', [
                'user' => $request->user,
                'request' => $request,
            ], function ($message) use ($request) {
                $message->to($request->user->email)
                    ->subject('Service Request Submitted - Control #'.$request->control_number)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });

            $notification->markAsSent('Email sent successfully');
        } catch (Exception $e) {
            $notification->markAsFailed($e->getMessage());
        }

        return $notification;
    }

    /**
     * Send email notification for status update
     */
    public function sendStatusUpdateNotification(ServiceRequest $request, string $oldStatus, string $newStatus, ?string $remarks = null): Notification
    {
        $statusMessages = [
            'submitted' => 'Your request has been submitted and is under review.',
            'pending' => 'Your request has been received and is queued for processing.',
            'processing' => 'Your request is currently being processed by our team.',
            'awaiting_requester_info' => 'MIS needs additional information from you before work can continue. Please open the request and send your response.',
            'awaiting_confirmation' => 'The technician has completed the work. Please confirm whether your concern is resolved.',
            'completed' => 'Your request has been completed successfully!',
            'closed' => 'Your request has been confirmed and closed.',
            'reopened' => 'Your request has been reopened for additional work.',
            'escalated' => 'Your request requires additional MIS review.',
            'cancelled' => 'Your request has been cancelled.',
            'rejected' => 'Your request has been rejected and cannot be processed at this time.',
        ];

        $notification = Notification::create([
            'service_request_id' => $request->id,
            'user_id' => $request->user_id,
            'recipient_email' => $request->user->email,
            'notification_type' => 'status_update',
            'subject' => 'Service Request Status Update - Control #'.$request->control_number,
            'message' => $statusMessages[$newStatus] ?? "Your request status has been updated to: $newStatus",
            'status' => 'pending',
        ]);

        $isReminder = in_array($newStatus, ['awaiting_requester_info', 'awaiting_confirmation'], true);
        if (! Setting::get('notify_request_updates', true) || ! $this->allowsEmail($request, true, $isReminder)) {
            $notification->markAsSent('Email disabled by administrator setting; in-app notification retained.');

            return $notification;
        }

        try {
            Mail::send('emails.status-update', [
                'user' => $request->user,
                'request' => $request,
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus,
                'remarks' => $remarks,
                'statusMessage' => $statusMessages[$newStatus] ?? "Status: $newStatus",
            ], function ($message) use ($request) {
                $message->to($request->user->email)
                    ->subject('Service Request Status Update - Control #'.$request->control_number)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });

            $notification->markAsSent('Email sent successfully');
        } catch (Exception $e) {
            $notification->markAsFailed($e->getMessage());
        }

        return $notification;
    }

    /**
     * Send email notification for request completion
     */
    public function sendRequestCompletedNotification(ServiceRequest $request): Notification
    {
        $notification = Notification::create([
            'service_request_id' => $request->id,
            'user_id' => $request->user_id,
            'recipient_email' => $request->user->email,
            'notification_type' => 'request_completed',
            'subject' => 'Service Request Completed - Control #'.$request->control_number,
            'message' => 'Your service request has been completed successfully.',
            'status' => 'pending',
        ]);

        if (! Setting::get('notify_completed_requests', true) || ! $this->allowsEmail($request, true)) {
            $notification->markAsSent('Email disabled by administrator setting; in-app notification retained.');

            return $notification;
        }

        try {
            Mail::send('emails.request-completed', [
                'user' => $request->user,
                'request' => $request,
            ], function ($message) use ($request) {
                $message->to($request->user->email)
                    ->subject('Service Request Completed - Control #'.$request->control_number)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });

            $notification->markAsSent('Email sent successfully');
        } catch (Exception $e) {
            $notification->markAsFailed($e->getMessage());
        }

        return $notification;
    }

    /**
     * Send email notification for request rejection
     */
    public function sendRequestRejectedNotification(ServiceRequest $request, string $reason = ''): Notification
    {
        $notification = Notification::create([
            'service_request_id' => $request->id,
            'user_id' => $request->user_id,
            'recipient_email' => $request->user->email,
            'notification_type' => 'request_rejected',
            'subject' => 'Service Request Cannot Be Processed - Control #'.$request->control_number,
            'message' => "Your service request could not be processed. Reason: $reason",
            'status' => 'pending',
        ]);

        if (! Setting::get('notify_request_updates', true) || ! $this->allowsEmail($request, true)) {
            $notification->markAsSent('Email disabled by administrator setting; in-app notification retained.');

            return $notification;
        }

        try {
            Mail::send('emails.request-rejected', [
                'user' => $request->user,
                'request' => $request,
                'reason' => $reason,
            ], function ($message) use ($request) {
                $message->to($request->user->email)
                    ->subject('Service Request Cannot Be Processed - Control #'.$request->control_number)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });

            $notification->markAsSent('Email sent successfully');
        } catch (Exception $e) {
            $notification->markAsFailed($e->getMessage());
        }

        return $notification;
    }

    /**
     * Send email notification for technician assignment
     */
    public function sendTechnicianAssignmentNotification(ServiceRequest $request, $technician): Notification
    {
        $technicianUser = $technician->user;
        $requestUser = $request->user;

        // Validate email address
        if (! $technicianUser || ! $technicianUser->email) {
            Log::error('Cannot send technician assignment email: Missing technician user or email', [
                'technician_id' => $technician->id,
                'has_user' => (bool) $technicianUser,
                'has_email' => $technicianUser ? (bool) $technicianUser->email : false,
            ]);
            throw new Exception('Technician has no email address configured');
        }

        $notification = Notification::create([
            'service_request_id' => $request->id,
            'user_id' => $technicianUser->id,
            'recipient_email' => $technicianUser->email,
            'notification_type' => 'technician_assigned',
            'subject' => 'New Service Request Assigned - Control #'.$request->control_number,
            'message' => 'You have been assigned to handle a new service request.',
            'status' => 'pending',
        ]);

        try {
            Log::info('Attempting to send technician assignment email', [
                'technician_email' => $technicianUser->email,
                'request_id' => $request->id,
                'control_number' => $request->control_number,
            ]);

            Mail::send('emails.technician-assigned', [
                'technician' => $technician,
                'request' => $request,
                'requester' => $requestUser,
            ], function ($message) use ($technicianUser, $request) {
                $message->to($technicianUser->email)
                    ->subject('New Service Request Assigned - Control #'.$request->control_number)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });

            $notification->markAsSent('Email sent successfully');
            Log::info('Technician assignment email sent successfully', [
                'technician_email' => $technicianUser->email,
            ]);
        } catch (Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error('Failed to send technician assignment email', [
                'technician_email' => $technicianUser->email,
                'request_id' => $request->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        return $notification;
    }

    public function sendNewMessageNotification(ServiceRequest $request, Message $message): Notification
    {
        $notification = Notification::create([
            'service_request_id' => $request->id,
            'user_id' => $request->user_id,
            'recipient_email' => $request->user?->email,
            'notification_type' => 'client_update',
            'subject' => 'New message from MIS',
            'message' => $request->control_number.': '.Str::limit($message->message, 180),
            'action_url' => route('user.messenger', ['request' => $request->id]),
            'status' => 'pending',
        ]);

        if (! Setting::get('notify_request_updates', true) || ! $this->allowsEmail($request, true)) {
            $notification->markAsSent('Email disabled; in-app notification retained.');

            return $notification;
        }

        try {
            Mail::send('emails.generic-notification', ['notification' => $notification], function ($mail) use ($notification) {
                $mail->to($notification->recipient_email)
                    ->subject($notification->subject)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });
            $notification->markAsSent('Email sent successfully.');
        } catch (Exception $e) {
            $notification->markAsFailed($e->getMessage());
        }

        return $notification;
    }

    /**
     * Retry sending failed notifications
     */
    public function retrySendingFailedNotifications(): int
    {
        $failedNotifications = Notification::where('status', 'failed')->get();
        $successCount = 0;

        foreach ($failedNotifications as $notification) {
            try {
                Mail::send('emails.generic-notification', [
                    'notification' => $notification,
                ], function ($message) use ($notification) {
                    $message->to($notification->recipient_email)
                        ->subject($notification->subject)
                        ->replyTo(Setting::get('support_email', config('mail.from.address')))
                        ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
                });

                $notification->markAsSent('Resent successfully');
                $successCount++;
            } catch (Exception $e) {
                $notification->markAsFailed('Retry failed: '.$e->getMessage());
            }
        }

        return $successCount;
    }

    public function retryNotification(Notification $notification): bool
    {
        if ($notification->status !== 'failed' || ! $notification->recipient_email) {
            return false;
        }
        if ($notification->user && $notification->user->notify_email_enabled === false) {
            return false;
        }

        try {
            Mail::send('emails.generic-notification', ['notification' => $notification], function ($message) use ($notification) {
                $message->to($notification->recipient_email)
                    ->subject($notification->subject)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });
            $notification->markAsSent('Retried successfully by administrator.');

            return true;
        } catch (Exception $e) {
            $notification->markAsFailed($e->getMessage());

            return false;
        }
    }

    private function allowsEmail(ServiceRequest $request, bool $requestUpdate = false, bool $reminder = false): bool
    {
        $user = $request->user;
        if (! $user || $user->notify_email_enabled === false) {
            return false;
        }
        if ($reminder && $user->notify_request_reminders === false) {
            return false;
        }

        return ! $requestUpdate || $user->notify_request_updates !== false;
    }
}
