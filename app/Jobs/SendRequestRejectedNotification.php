<?php

namespace App\Jobs;

use App\Models\ServiceRequest;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRequestRejectedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly string $reason
    ) {}

    public function handle(): void
    {
        $request = $this->serviceRequest;

        if (!$request->user || !$request->user->email) {
            Log::warning("Cannot send rejection notification: user not found", ['request_id' => $request->id]);
            return;
        }

        $notification = Notification::create([
            'service_request_id' => $request->id,
            'user_id' => $request->user_id,
            'recipient_email' => $request->user->email,
            'notification_type' => 'request_rejected',
            'subject' => "Service Request Rejected - Control #" . $request->control_number,
            'message' => "Your service request has been rejected. Reason: " . $this->reason,
            'status' => 'pending',
        ]);

        try {
            Mail::send('emails.request-rejected', [
                'user' => $request->user,
                'request' => $request,
                'reason' => $this->reason,
            ], function ($message) use ($request) {
                $message->to($request->user->email)
                    ->subject("Service Request Rejected - Control #" . $request->control_number)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });

            $notification->markAsSent('Rejection email sent');
            Log::info("Request rejected notification sent", ['request_id' => $request->id]);
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error("Failed to send rejection notification", [
                'request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendRequestRejectedNotification job failed', [
            'request_id' => $this->serviceRequest->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
