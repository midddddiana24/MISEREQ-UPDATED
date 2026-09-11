<?php

namespace App\Jobs;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotifyAdminNewRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly User $requester
    ) {}

    public function handle(): void
    {
        if (!Setting::get('notify_admin_alerts', true)) {
            return;
        }

        $admins = User::where('is_active', true)
            ->where(fn($query) => $query->where('role', 'admin')->orWhere('is_admin', true))
            ->pluck('email')
            ->toArray();

        if (empty($admins)) {
            return;
        }

        try {
            Mail::send('emails.admin-request-submitted', [
                'request' => $this->serviceRequest,
                'user' => $this->requester,
            ], function ($message) use ($admins) {
                $message->to($admins)
                    ->subject("New Service Request Submitted - Control #" . $this->serviceRequest->control_number)
                    ->replyTo(Setting::get('support_email', config('mail.from.address')))
                    ->from(config('mail.from.address', 'noreply@example.com'), config('mail.from.name', 'MIS Service Request'));
            });

            Log::info("Admin notification sent for new request", [
                'request_id' => $this->serviceRequest->id,
                'admin_count' => count($admins),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send admin notification", [
                'request_id' => $this->serviceRequest->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('NotifyAdminNewRequest job failed', [
            'request_id' => $this->serviceRequest->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
