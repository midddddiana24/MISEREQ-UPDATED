<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestSystemMail extends Command
{
    protected $signature = 'system:mail-test {--to= : Recipient address; defaults to the configured MIS support address}';

    protected $description = 'Send a safe SMTP delivery test without displaying mail credentials';

    public function handle(): int
    {
        $recipient = (string) ($this->option('to')
            ?: Setting::get('support_email', config('mail.from.address')));

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid support or --to email address is required.');

            return self::FAILURE;
        }

        try {
            Mail::raw(
                "WVSU MIS Service Request System email delivery test.\n\n"
                .'Server time: '.now()->format('M d, Y h:i A')."\n"
                .'Environment: '.app()->environment(),
                fn ($message) => $message
                    ->to($recipient)
                    ->subject('WVSU MIS email delivery test')
            );

            $this->info("Test email accepted for delivery to {$recipient}.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('SMTP delivery test failed. Review the protected application log.');

            return self::FAILURE;
        }
    }
}
