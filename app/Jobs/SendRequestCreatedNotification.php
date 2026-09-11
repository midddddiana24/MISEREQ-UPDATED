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

class SendRequestCreatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly ServiceRequest $serviceRequest
    ) {}

    public function handle(): void
    {
        $request = $this->serviceRequest;

        $notification = Notification::create([
            'service_request_id' => $request->id,
            'user_id' => $request->user_id,
            'recipient_email' => $request->user->email,
            'notification_type' => 'request_created',
            'subject' => "Service Request Submitted - Control #" . $request->control_number,
            'message' => "Your service request has been successfully submitted.",
            'status' => 'pending',
        ]);

        if (!Setting::get('notify_new_requests', true) || !$request->user) {
            $notification->markAsSent('Email disabled or user not found.');
            return;
        }

        try {
            Mail::send('emails.request-created', [
                'user' => $request->user,
                'request' => $request,
            ], function ($message) use ($request) {
                $message->to($request->user->email)
                    ->subject("Service Request Submitted - Control #" . $request->control_number)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });

            $notification->markAsSent('Email sent successfully');
            Log::info("Request created notification sent", ['request_id' => $request->id]);
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error("Failed to send request created notification", [
                'request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendRequestCreatedNotification job failed', [
            'request_id' => $this->serviceRequest->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
