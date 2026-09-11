<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\PasswordHistory;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserSession;
use App\Rules\NotInPasswordHistory;
use App\Rules\StrongPassword;
use App\Services\SecurityNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('user.profile', [
            'user' => $user,
            'twoFactorEnabled' => $user->isTwoFactorEnabled(),
            'loginAttempts' => $this->loginAttempts($user, 10),
        ]);
    }

    public function settings(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $currentSessionId = $request->session()->getId();
        $sessionLifetime = max(15, min(240, (int) Setting::get('client_session_timeout', 120)));
        $sessions = UserSession::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where('last_activity_at', '>=', now()->subMinutes($sessionLifetime))
            ->latest('last_activity_at')
            ->get()
            ->each(function (UserSession $session) use ($currentSessionId) {
                $session->setAttribute('is_current', hash_equals($currentSessionId, $session->session_id));
            });

        return view('user.settings', [
            'user' => $user,
            'twoFactorEnabled' => $user->isTwoFactorEnabled(),
            'sessions' => $sessions,
            'loginAttempts' => $this->loginAttempts($user, 8),
            'passwordChangedAt' => PasswordHistory::where('user_id', $user->id)->latest()->value('changed_at'),
            'remainingRecoveryCodeCount' => count($user->two_factor_recovery_code ?? []),
        ]);
    }

    public function security(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('user.security', [
            'user' => $user,
            'loginAttempts' => $this->loginAttempts($user, 20),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$request->user()->id,
        ]);

        $request->user()->update($request->only('name', 'email'));

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', new StrongPassword, new NotInPasswordHistory(Auth::id())],
        ]);

        /** @var User $user */
        $user = $request->user();
        $hashedPassword = Hash::make($request->password);

        DB::transaction(function () use ($user, $hashedPassword, $request) {
            $user->update([
                'password' => $hashedPassword,
                'password_expires_at' => Carbon::now()->addDays(90),
            ]);
            PasswordHistory::create([
                'user_id' => $user->id,
                'password_hash' => $hashedPassword,
            ]);
            UserSession::where('user_id', $user->id)
                ->where('session_id', '!=', $request->session()->getId())
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        });
        $request->session()->forget('auth.password_confirmed_at');

        app(SecurityNotificationService::class)->notify(
            $user->fresh(),
            'Your MIS password was changed',
            'Your password was changed and other active sessions were signed out. If this was not you, contact the MIS Office immediately.'
        );

        return redirect()->route('user.settings')->with('success', 'Password updated and other devices signed out.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:2',
            'office' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone_local' => 'nullable|string|max:20',
            'current_password' => ['required', 'current_password'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->update($request->only(
            'first_name',
            'last_name',
            'middle_initial',
            'office',
            'designation',
            'phone_local'
        ));

        app(SecurityNotificationService::class)->notify(
            $user,
            'Your MIS profile was updated',
            'Your account profile information was changed. If this was not you, contact the MIS Office.'
        );

        return redirect()->route('user.profile')->with('success', 'Profile updated successfully.');
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->update([
            'notify_email_enabled' => $request->boolean('notify_email_enabled'),
            'notify_request_updates' => $request->boolean('notify_request_updates'),
            'notify_request_reminders' => $request->boolean('notify_request_reminders'),
            'notify_security_alerts' => $request->boolean('notify_security_alerts'),
        ]);

        return redirect()->route('user.settings')->with('success', 'Notification preferences updated.');
    }

    public function terminateSession(Request $request, UserSession $session): RedirectResponse
    {
        abort_unless($session->user_id === $request->user()->id, 403);

        if (hash_equals($request->session()->getId(), $session->session_id)) {
            return back()->with('error', 'Use Logout to end your current session.');
        }

        $session->revoke();
        app(SecurityNotificationService::class)->notify(
            $request->user(),
            'A signed-in device was removed',
            "The session for {$session->device_name} was signed out from your account settings."
        );

        return back()->with('success', 'The selected device has been signed out.');
    }

    public function terminateOtherSessions(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $count = UserSession::where('user_id', $request->user()->id)
            ->where('session_id', '!=', $request->session()->getId())
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        app(SecurityNotificationService::class)->notify(
            $request->user(),
            'Other devices were signed out',
            "{$count} other active session(s) were signed out from your MIS account."
        );

        return back()->with('success', "{$count} other device(s) signed out.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|current_password']);
        $user = $request->user();

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function loginAttempts(User $user, int $limit)
    {
        return LoginAttempt::whereIn('username', array_values(array_unique(array_filter([
            $user->username,
            $user->email,
        ]))))
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
