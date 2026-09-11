<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ServiceRequest;
use App\Services\EmailNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestUserStatusNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-user-status-notification {user_id} {request_id} {new_status?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending user status update email notification. Usage: php artisan email:test-user-status-notification {user_id} {request_id} {new_status?}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        $requestId = (int) $this->argument('request_id');
        $newStatus = $this->argument('new_status') ?? 'processing';

        $this->info("🔍 Testing user status update email notification...");
        $this->info("User ID: $userId");
        $this->info("Request ID: $requestId");
        $this->info("New Status: $newStatus");

        // Get user
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User not found with ID: $userId");
            return 1;
        }

        $this->info("✅ User found: {$user->first_name} {$user->last_name}");

        // Check email
        if (!$user->email) {
            $this->error("❌ User has no email address configured");
            return 1;
        }

        $this->info("✅ User email: {$user->email}");

        // Get service request
        $serviceRequest = ServiceRequest::find($requestId);
        if (!$serviceRequest) {
            $this->error("❌ Service request not found with ID: $requestId");
            return 1;
        }

        $this->info("✅ Service request found: Control #{$serviceRequest->control_number}");
        $this->info("   Current Status: {$serviceRequest->status}");

        // Verify request belongs to user
        if ($serviceRequest->user_id !== $userId) {
            $this->error("❌ Service request does not belong to this user!");
            return 1;
        }

        $this->info("✅ Request verified as belonging to user");

        // Test mail configuration
        $this->info("\n📧 Mail Configuration:");
        $this->info("  MAIL_MAILER: " . config('mail.default'));
        $this->info("  MAIL_HOST: " . config('mail.mailers.smtp.host'));
        $this->info("  MAIL_PORT: " . config('mail.mailers.smtp.port'));
        $this->info("  MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption'));
        $this->info("  MAIL_FROM_ADDRESS: " . config('mail.from.address'));

        // Attempt to send email
        try {
            $this->info("\n📤 Triggering status update and sending notification email...");

            $emailService = new EmailNotificationService();
            $oldStatus = $serviceRequest->status;

            // Update status
            $serviceRequest->update(['status' => $newStatus]);

            // Send notification
            $notification = $emailService->sendStatusUpdateNotification(
                $serviceRequest,
                $oldStatus,
                $newStatus,
                'Test status update notification'
            );

            $this->info("✅ Email sent successfully!");
            $this->info("   Notification ID: {$notification->id}");
            $this->info("   Status: {$notification->status}");
            $this->info("   Recipient: {$notification->recipient_email}");
            $this->info("   Type: {$notification->notification_type}");
            $this->info("   Message: {$notification->message}");
            $this->info("\n✅ Request status updated from '$oldStatus' to '$newStatus'");
            $this->info("\n📬 Check your email at: {$user->email}");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error sending email: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            Log::error('Test user status notification error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'request_id' => $requestId,
                'exception' => $e
            ]);
            return 1;
        }
    }
}
