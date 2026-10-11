<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class AdminSettingsController extends Controller
{
    public function __construct(private readonly AdminActivityLogger $activityLogger) {}

    public function index()
    {
        $adminQuery = User::query()
            ->where(function ($query) {
                $query->where('is_admin', true)->orWhere('role', 'admin');
            });

        return view('admin.settings', [
            'systemSettings' => Setting::getByCategory('system'),
            'notificationSettings' => Setting::getByCategory('notifications'),
            'securitySettings' => Setting::getByCategory('security'),
            'settingsLastUpdated' => Setting::query()->max('updated_at'),
            'adminCount' => (clone $adminQuery)->count(),
            'adminsWithTwoFactor' => (clone $adminQuery)->whereNotNull('two_fa_enabled_at')->count(),
            'mailDriver' => (string) config('mail.default', 'Not configured'),
            'clientTimeoutOptions' => $this->clientTimeoutOptions(),
            'sessionLifetimeMinutes' => (int) config('session.lifetime', 30),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'support_email' => ['required', 'email', 'max:255'],
            'max_upload_size' => ['required', 'integer', Rule::in([5, 10, 15, 20])],
            'client_session_timeout' => ['required', 'integer', Rule::in($this->clientTimeoutOptions())],
            'max_active_client_sessions' => ['required', 'integer', Rule::in([1, 2, 3, 5])],
            'client_login_lock_attempts' => ['required', 'integer', Rule::in(User::CLIENT_LOGIN_ATTEMPT_OPTIONS)],
            'client_login_lock_minutes' => ['required', 'integer', Rule::in(User::CLIENT_LOGIN_LOCK_MINUTE_OPTIONS)],
        ]);

        $previousLockPolicy = [
            'attempts' => (int) Setting::get('client_login_lock_attempts', 5),
            'minutes' => (int) Setting::get('client_login_lock_minutes', 30),
        ];

        DB::transaction(function () use ($request, $validated, $previousLockPolicy) {
            Setting::set(
                'support_email',
                $validated['support_email'],
                'string',
                'system',
                'Reply-to and support address used by MIS notifications.'
            );
            Setting::set(
                'max_upload_size',
                $validated['max_upload_size'],
                'integer',
                'system',
                'Maximum size in megabytes for each supported upload.'
            );
            Setting::set(
                'notify_new_requests',
                $request->boolean('notify_new_requests'),
                'boolean',
                'notifications',
                'Send request submission confirmations to clients.'
            );
            Setting::set(
                'notify_request_updates',
                $request->boolean('notify_request_updates'),
                'boolean',
                'notifications',
                'Send request and Messenger update emails to clients.'
            );
            Setting::set(
                'notify_completed_requests',
                $request->boolean('notify_completed_requests'),
                'boolean',
                'notifications',
                'Send completion confirmation emails to clients.'
            );
            Setting::set(
                'notify_admin_alerts',
                $request->boolean('notify_admin_alerts'),
                'boolean',
                'notifications',
                'Create admin alerts for new requests and required actions.'
            );
            Setting::set(
                'require_2fa_admins',
                $request->boolean('require_2fa_admins'),
                'boolean',
                'security',
                'Require administrators to configure two-factor authentication.'
            );
            Setting::set(
                'require_2fa_clients',
                $request->boolean('require_2fa_clients'),
                'boolean',
                'security',
                'Require school client accounts to configure two-factor authentication.'
            );
            Setting::set(
                'client_session_timeout',
                $validated['client_session_timeout'],
                'integer',
                'security',
                'Client inactivity timeout in minutes.'
            );
            Setting::set(
                'max_active_client_sessions',
                $validated['max_active_client_sessions'],
                'integer',
                'security',
                'Maximum simultaneous sessions allowed for a client account.'
            );
            Setting::set(
                'client_login_lock_attempts',
                $validated['client_login_lock_attempts'],
                'integer',
                'security',
                'Client failed-login threshold within a 30-minute observation window.'
            );
            Setting::set(
                'client_login_lock_minutes',
                $validated['client_login_lock_minutes'],
                'integer',
                'security',
                'Temporary client account-lock duration in minutes.'
            );
            $newLockPolicy = [
                'attempts' => $validated['client_login_lock_attempts'],
                'minutes' => $validated['client_login_lock_minutes'],
            ];
            if ($previousLockPolicy !== $newLockPolicy) {
                $this->activityLogger->logAdministrative(
                    'client_login_lock_policy_changed',
                    'Client login lock policy changed in System Settings.',
                    $previousLockPolicy,
                    $newLockPolicy
                );
            }
            Setting::set(
                'security_login_alerts',
                $request->boolean('security_login_alerts'),
                'boolean',
                'notifications',
                'Email optional account activity; mandatory security emails remain enabled.'
            );
        });

        return redirect()
            ->route('admin.settings')
            ->with('success', 'System settings saved successfully.');
    }

    public function sendTestEmail()
    {
        $recipient = (string) Setting::get('support_email', config('mail.from.address'));

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return redirect()
                ->route('admin.settings')
                ->with('error', 'Save a valid MIS support email before sending a test.');
        }

        try {
            Mail::raw(
                "This test confirms that the WVSU MIS Service Request System can send email using the current server configuration.\n\nSent: ".now()->format('M d, Y h:i A'),
                function ($message) use ($recipient) {
                    $message->to($recipient)
                        ->subject('WVSU MIS email configuration test')
                        ->replyTo($recipient)
                        ->from(
                            config('mail.from.address', 'noreply@example.com'),
                            config('mail.from.name', 'MIS Service Request')
                        );
                }
            );

            $mailer = (string) config('mail.default');
            $message = in_array($mailer, ['array', 'log'], true)
                ? "Test email processed by the {$mailer} mailer. No external email was delivered."
                : "Test email handed to the {$mailer} mailer for {$recipient}. Check the inbox to confirm delivery.";

            return redirect()->route('admin.settings')->with('success', $message);
        } catch (Throwable $exception) {
            Log::error('Admin settings email test failed', [
                'admin_id' => auth()->id(),
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.settings')
                ->with('error', 'The test email could not be sent. Check the protected mail-server configuration.');
        }
    }

    private function clientTimeoutOptions(): array
    {
        $sessionLifetime = (int) config('session.lifetime', 30);

        return array_values(array_filter([15, 30, 60, 120, 240], fn (int $minutes) => $minutes <= $sessionLifetime));
    }
}
