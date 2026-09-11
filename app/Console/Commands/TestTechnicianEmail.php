<?php

namespace App\Console\Commands;

use App\Models\Technician;
use App\Services\EmailNotificationService;
use App\Models\ServiceRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestTechnicianEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-technician-notification {technician_id} {request_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending technician assignment email. Usage: php artisan email:test-technician-notification {technician_id} {request_id}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $technicianId = $this->argument('technician_id');
        $requestId = $this->argument('request_id');

        $this->info("🔍 Testing technician assignment email notification...");
        $this->info("Technician ID: $technicianId");
        $this->info("Request ID: $requestId");

        // Get technician
        $technician = Technician::with('user')->find($technicianId);
        if (!$technician) {
            $this->error("❌ Technician not found with ID: $technicianId");
            return 1;
        }

        $this->info("✅ Technician found: {$technician->user->full_name}");

        // Check email
        if (!$technician->user->email) {
            $this->error("❌ Technician has no email address configured");
            return 1;
        }

        $this->info("✅ Technician email: {$technician->user->email}");

        // Get service request
        $serviceRequest = ServiceRequest::find($requestId);
        if (!$serviceRequest) {
            $this->error("❌ Service request not found with ID: $requestId");
            return 1;
        }

        $this->info("✅ Service request found: Control #{$serviceRequest->control_number}");

        // Test mail configuration
        $this->info("\n📧 Mail Configuration:");
        $this->info("  MAIL_MAILER: " . config('mail.default'));
        $this->info("  MAIL_HOST: " . config('mail.mailers.smtp.host'));
        $this->info("  MAIL_PORT: " . config('mail.mailers.smtp.port'));
        $this->info("  MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption'));
        $this->info("  MAIL_FROM_ADDRESS: " . config('mail.from.address'));

        // Attempt to send email
        try {
            $this->info("\n📤 Sending test email...");

            $emailService = new EmailNotificationService();
            $notification = $emailService->sendTechnicianAssignmentNotification($serviceRequest, $technician);

            $this->info("✅ Email sent successfully!");
            $this->info("Notification ID: {$notification->id}");
            $this->info("Status: {$notification->status}");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error sending email: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
