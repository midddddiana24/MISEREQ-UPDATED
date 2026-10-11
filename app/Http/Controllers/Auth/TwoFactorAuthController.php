<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminDeviceTrustService;
use App\Services\SecurityNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAService;

class TwoFactorAuthController extends Controller
{
    public function showSetup(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isTwoFactorEnabled()) {
            return redirect()->route($this->settingsRoute($user))->with('info', '2FA is already enabled.');
        }

        $secret = $this->startPendingSetup($request);

        return view('auth.2fa-setup', [
            'qrCodeInline' => $this->qrCode($user, $secret),
            'secret' => $secret,
        ]);
    }

    public function storeSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'verification_code' => ['required', 'string', 'digits:6'],
        ]);

        $request->session()->passwordConfirmed();

        return $this->completeSetup($request, route('2fa.setup'));
    }

    public function beginSettingsSetup(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        /** @var User $user */
        $user = $request->user();
        if ($user->isTwoFactorEnabled()) {
            return response()->json(['error' => '2FA is already enabled.'], 422);
        }

        $request->session()->passwordConfirmed();
        $secret = $this->startPendingSetup($request, true);

        return response()->json([
            'secret' => $secret,
            'qr_code' => $this->qrCode($user, $secret),
        ]);
    }

    public function confirmSettingsSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'verification_code' => ['required', 'string', 'digits:6'],
        ]);

        return $this->completeSetup($request, route('user.settings'));
    }

    public function recoveryCodes(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->isTwoFactorEnabled()) {
            return redirect()->route($this->settingsRoute($user))->with('error', 'Enable 2FA before managing recovery codes.');
        }

        return view('user.2fa-recovery-codes', [
            'user' => $user,
            'recoveryCodes' => $request->session()->get('new_recovery_codes', []),
            'remainingCodeCount' => count($user->two_factor_recovery_code ?? []),
        ]);
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'verification_code' => ['required', 'string', 'digits:6'],
        ]);

        /** @var User $user */
        $user = $request->user();
        if (! $user->isTwoFactorEnabled() || ! $this->validAuthenticatorCode($user, $request->verification_code)) {
            return back()->withErrors(['verification_code' => 'The authenticator code is invalid.']);
        }

        $codes = $this->generateRecoveryCodes();
        $this->storeHashedRecoveryCodes($user, $codes);
        app(SecurityNotificationService::class)->notify(
            $user,
            'New 2FA recovery codes were generated',
            'Your previous recovery codes no longer work. If this was not you, contact the MIS Office immediately.'
        );

        return redirect()->route('settings.2fa.recovery-codes')
            ->with('new_recovery_codes', $codes)
            ->with('success', 'New recovery codes generated. Save them now; they will not be shown again.');
    }

    public function disableSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'verification_code' => ['required', 'string', 'digits:6'],
        ]);

        /** @var User $user */
        $user = $request->user();
        if (! $user->isTwoFactorEnabled() || ! $this->validAuthenticatorCode($user, $request->verification_code)) {
            return back()->withErrors(['verification_code' => 'The authenticator code is invalid.']);
        }

        $user->disable2FA();
        $request->session()->forget(['2fa_pending_secret', '2fa_pending_started_at']);
        app(SecurityNotificationService::class)->notifyMandatory(
            SecurityNotificationService::EVENT_TWO_FACTOR_DISABLED,
            $user,
            'Two-factor authentication was disabled',
            '2FA was removed from your MIS account. If this was not you, reset your password and contact the MIS Office immediately.'
        );

        return redirect()->route($this->settingsRoute($user))->with('success', 'Two-factor authentication disabled.');
    }

    public function showVerify()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        /** @var User $user */
        $user = Auth::user();
        if (! $user->isTwoFactorEnabled()) {
            return redirect()->route($this->dashboardRoute($user))->with('info', 'Two-factor authentication is not enabled.');
        }

        return view('auth.2fa-verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        if (! Auth::check()) {
            return redirect()->route('login')->withErrors(['2fa' => 'Please sign in first.']);
        }

        /** @var User $user */
        $user = Auth::user();
        if (! $user->isTwoFactorEnabled()) {
            return redirect()->route($this->dashboardRoute($user));
        }

        $code = strtoupper(trim((string) $request->code));
        $valid = preg_match('/^\d{6}$/', $code)
            ? $this->validAuthenticatorCode($user, $code)
            : $this->verifyRecoveryCode($user, $code);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid authenticator or recovery code.']);
        }

        $request->session()->forget(['2fa_user_id', '2fa_pending']);
        $request->session()->put('2fa_verified', true);
        $request->session()->regenerate();
        app(AdminDeviceTrustService::class)->updateChallengeSession($request);

        if ($user->isAdmin()) {
            $request->session()->forget('url.intended');
            if ($request->session()->has('admin_device_challenge_id')
                || (bool) $request->session()->get('admin_device_bootstrap', false)) {
                return redirect()->route('admin.device.verify')
                    ->with('success', 'Two-factor authentication verified. Confirm this device to continue.');
            }

            return redirect()->route('admin.dashboard')
                ->with('success', 'Two-factor authentication verified.');
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Two-factor authentication verified.');
    }

    private function completeSetup(Request $request, string $failureRedirect): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $secret = (string) $request->session()->get('2fa_pending_secret', '');
        $startedAt = (int) $request->session()->get('2fa_pending_started_at', 0);

        if (! $secret || ! $startedAt || $startedAt < now()->subMinutes(10)->timestamp) {
            $request->session()->forget(['2fa_pending_secret', '2fa_pending_started_at']);

            return redirect($failureRedirect)->with('error', 'The setup session expired. Start 2FA setup again.');
        }

        $google2fa = new Google2FAService;
        if (! $google2fa->verifyKey($secret, $request->verification_code, 1)) {
            return back()->withErrors(['verification_code' => 'The authenticator code is invalid.']);
        }

        $codes = $this->generateRecoveryCodes();
        $user->enable2FA($secret);
        $this->storeHashedRecoveryCodes($user, $codes);
        $request->session()->forget(['2fa_pending_secret', '2fa_pending_started_at']);
        $request->session()->put('2fa_verified', true);
        app(SecurityNotificationService::class)->notify(
            $user,
            'Two-factor authentication was enabled',
            '2FA was enabled for your MIS account. If this was not you, contact the MIS Office immediately.'
        );

        return redirect()->route('settings.2fa.recovery-codes')
            ->with('new_recovery_codes', $codes)
            ->with('success', 'Two-factor authentication enabled. Save your recovery codes now.');
    }

    private function startPendingSetup(Request $request, bool $forceNew = false): string
    {
        $existing = (string) $request->session()->get('2fa_pending_secret', '');
        $startedAt = (int) $request->session()->get('2fa_pending_started_at', 0);
        if (! $forceNew && $existing && $startedAt >= now()->subMinutes(10)->timestamp) {
            return $existing;
        }

        $secret = (new Google2FAService)->generateSecretKey();
        $request->session()->put('2fa_pending_secret', $secret);
        $request->session()->put('2fa_pending_started_at', now()->timestamp);

        return $secret;
    }

    private function qrCode(User $user, string $secret): string
    {
        $qrCode = (new Google2FAService)->getQRCodeInline(
            config('app.name', 'MIS Service Request'),
            $user->email,
            $secret,
            200
        );

        return str_starts_with($qrCode, 'data:image')
            ? $qrCode
            : 'data:image/svg+xml;base64,'.base64_encode($qrCode);
    }

    private function validAuthenticatorCode(User $user, string $code): bool
    {
        return (new Google2FAService)->verifyKey($user->google2fa_secret, $code, 1);
    }

    private function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => strtoupper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }

    private function storeHashedRecoveryCodes(User $user, array $codes): void
    {
        $user->two_factor_recovery_code = array_map(fn (string $code) => Hash::make($code), $codes);
        $user->save();
    }

    private function verifyRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_code ?? [];

        foreach ($codes as $index => $storedCode) {
            $matches = str_starts_with((string) $storedCode, '$2')
                ? Hash::check($code, $storedCode)
                : hash_equals(strtoupper((string) $storedCode), $code);

            if ($matches) {
                unset($codes[$index]);
                $user->two_factor_recovery_code = array_values($codes);
                $user->save();
                app(SecurityNotificationService::class)->notify(
                    $user,
                    'A 2FA recovery code was used',
                    'A one-time recovery code was used to sign in to your MIS account.'
                );

                return true;
            }
        }

        return false;
    }

    private function dashboardRoute(User $user): string
    {
        return $user->isAdmin() ? 'admin.dashboard' : 'dashboard';
    }

    private function settingsRoute(User $user): string
    {
        return $user->isAdmin() ? 'admin.settings' : 'user.settings';
    }
}
