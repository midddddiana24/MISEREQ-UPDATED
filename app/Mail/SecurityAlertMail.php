<?php

namespace App\Mail;

use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SecurityAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Notification $notification) {}

    public function build(): self
    {
        return $this->subject($this->notification->subject)
            ->replyTo(Setting::get('support_email', config('mail.from.address')))
            ->from(
                config('mail.from.address', 'noreply@example.com'),
                config('mail.from.name', 'MIS Service Request')
            )
            ->view('emails.generic-notification');
    }
}
