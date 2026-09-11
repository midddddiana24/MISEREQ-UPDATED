<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSession;
use App\Services\SecurityNotificationService;
use App\Services\AdminDeviceTrustService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show login form
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validate username + password
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $identifier = trim($request->username);
        $loginField = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $rateKey = 'login:' . Str::lower($identifier) . '|' . $request->ip();
        $user = User::where($loginField, $identifier)->first();

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $this->recordAttempt($identifier, $request, false, 'Rate limited');
            return back()->withErrors([
                'username' => 'Too many login attempts. Try again in ' . RateLimiter::availableIn($rateKey) . ' seconds.',
            ])->onlyInput('username')->with('openLogin', true);
        }

        if ($user?->isAccountLocked()) {
            $this->recordAttempt($identifier, $request, false, 'Account locked');
            return back()->withErrors([
                'username' => 'This account is temporarily locked. Please try again later.',
            ])->onlyInput('username')->with('openLogin', true);
        }

        if (!Auth::attempt(
            [$loginField => $identifier, 'password' => $request->password, 'is_active' => true],
            $request->boolean('remember')
        )) {
            RateLimiter::hit($rateKey, 30 * 60);
            $user?->incrementFailedLoginAttempts();
            $this->recordAttempt($identifier, $request, false, $user && !$user->is_active ? 'Account inactive' : 'Invalid credentials');
            if ($user && $user->fresh()->isAccountLocked()) {
                app(SecurityNotificationService::class)->notify(
                    $user,
                    'Your MIS account was temporarily locked',
                    'Multiple unsuccessful sign-in attempts caused a temporary account lock. If this was not you, contact the MIS Office.'
                );
            }
            Log::channel('stack')->warning('Failed login: ' . $identifier);
            return back()->withErrors([
                'username' => 'Invalid username or password.',
            ])->onlyInput('username')->with('openLogin', true);
        }

        // Regenerate session to prevent session fixation attacks
        $request->session()->regenerate();

        // Get authenticated user
        $user = Auth::user();
        $knownDevice = LoginAttempt::whereIn('username', [$user->username, $user->email])
            ->where('success', true)
            ->where('user_agent', $request->userAgent())
            ->where('ip_address', $request->ip())
            ->exists();
        $user->resetFailedLoginAttempts();
        $user->updateLastActivity($request->ip(), $request->userAgent());
        RateLimiter::clear($rateKey);
        $this->recordAttempt($user->username, $request, true);
        if (!$knownDevice && !$user->isAdmin()) {
            app(SecurityNotificationService::class)->notify(
                $user,
                'New device signed in to your MIS account',
                'A new device signed in from IP ' . $request->ip() . ' at ' . now()->format('M d, Y h:i A') . '.'
            );
        }

        app(AdminDeviceTrustService::class)->beginLogin($user, $request);

        // Check if user has 2FA enabled
        if ($user->isTwoFactorEnabled()) {
            // Administrators must never inherit a stale client-dashboard
            // destination while completing two-factor authentication.
            if ($user->isAdmin()) {
                $request->session()->forget('url.intended');
            }

            // Store user ID in session for 2FA verification
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_pending', true);
            $request->session()->forget('2fa_verified');

            return redirect()->route('2fa.verify')
                ->with('info', 'Please enter your 2FA verification code to continue.');
        }

        // 2FA not enabled - user is fully authenticated
        $request->session()->put('2fa_verified', true);

        // Redirect based on role
        if ($user->isAdmin()) {
            $request->session()->forget('url.intended');
            if ($request->session()->has('admin_device_challenge_id')
                || (bool) $request->session()->get('admin_device_bootstrap', false)) {
                return redirect()->route('admin.device.verify');
            }
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }

    private function recordAttempt(string $username, Request $request, bool $success, ?string $reason = null): void
    {
        LoginAttempt::create([
            'username' => $username,
            'ip_address' => $request->ip(),
            'success' => $success,
            'failure_reason' => $reason,
            'user_agent' => $request->userAgent(),
            'device_info' => $request->header('Sec-CH-UA-Platform'),
        ]);
    }

    /**
     * Logout
     */
    public function destroy(Request $request): RedirectResponse
    {
        UserSession::where('session_id', $request->session()->getId())
            ->where('user_id', Auth::id())
            ->update(['revoked_at' => now()]);
        Auth::guard('web')->logout();

        // Clear 2FA session variables
        $request->session()->forget([
            '2fa_user_id',
            '2fa_pending',
            '2fa_verified',
            'admin_device_bootstrap',
            'admin_device_challenge_id',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
