<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\LoginAttempt;
use App\Models\TrustedAdminDevice;
use App\Models\User;
use App\Models\UserSession;
use App\Services\SecurityNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::query()->orderByDesc('created_at')->orderByDesc('id')->paginate(15);
        $userCounts = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'end_users' => User::where('role', 'user')->count(),
            'locked' => User::where('locked_until', '>', now())->count(),
        ];

        return view('admin.users.index', compact('users', 'userCounts'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'role' => 'required|in:user,admin',
        ]);

        // Hash password before creating
        $validated['password'] = bcrypt($validated['password']);

        $validated['is_admin'] = $validated['role'] === 'admin';
        $user = User::create($validated);
        $this->audit('user_created', "Account {$user->username} created.", ['user_id' => $user->id, 'role' => $user->role]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $loginAttempts = LoginAttempt::whereIn('username', [$user->username, $user->email])
            ->latest('created_at')
            ->limit(15)
            ->get();

        $activeSessions = $user->sessions()->whereNull('revoked_at')->latest('last_activity_at')->get();
        $trustedDevices = $user->trustedAdminDevices()->whereNull('revoked_at')->latest('last_used_at')->get();

        return view('admin.users.show', compact('user', 'loginAttempts', 'activeSessions', 'trustedDevices'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'username' => 'required|string|unique:users,username,'.$id,
            'role' => 'required|in:user,admin',
        ]);

        if ($user->isAdmin() && $validated['role'] !== 'admin' && $this->activeAdminCount() <= 1) {
            throw ValidationException::withMessages(['role' => 'The final active administrator cannot be demoted.']);
        }

        $oldRole = $user->role;
        $validated['is_admin'] = $validated['role'] === 'admin';
        $user->update($validated);
        $this->audit('user_updated', "Account {$user->username} updated.", [
            'user_id' => $user->id,
            'old_role' => $oldRole,
            'new_role' => $user->role,
        ]);

        return back()->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        // Prevent deleting the current logged-in user
        if ($id == auth()->id()) {
            return back()->with('error', 'You cannot delete your own account');
        }

        $user = User::findOrFail($id);
        if ($user->isAdmin() && $this->activeAdminCount() <= 1) {
            return back()->with('error', 'The final active administrator cannot be disabled.');
        }
        if (! $user->is_active) {
            return back()->with('info', 'This account is already disabled.');
        }

        $user->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivated_by' => Auth::id(),
        ]);
        DB::table('sessions')->where('user_id', $user->id)->delete();
        $this->audit('user_deactivated', "Account {$user->username} disabled.", ['user_id' => $user->id]);

        return back()->with('success', 'User account disabled. Existing records were preserved.');
    }

    public function activate(User $user)
    {
        $user->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_by' => null,
        ]);
        $this->audit('user_activated', "Account {$user->username} reactivated.", ['user_id' => $user->id]);

        return back()->with('success', 'User account reactivated.');
    }

    public function unlock(Request $request, User $user)
    {
        $validated = $request->validate([
            'unlock_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if (! $user->is_active) {
            return back()->with('error', 'Reactivate this account before removing its login lock.');
        }

        if (! $user->isAccountLocked() && (int) $user->failed_login_attempts === 0) {
            return back()->with('info', 'This account is not locked and has no failed-attempt counter to clear.');
        }

        $previousAttempts = (int) $user->failed_login_attempts;
        $previousLockedUntil = $user->locked_until?->toIso8601String();
        $this->clearLoginRateLimits($user);
        $user->resetFailedLoginAttempts();

        $this->audit('user_unlocked', "Account {$user->username} unlocked by an administrator.", [
            'user_id' => $user->id,
            'reason' => $validated['unlock_reason'],
            'previous_failed_attempts' => $previousAttempts,
            'previous_locked_until' => $previousLockedUntil,
        ]);

        app(SecurityNotificationService::class)->notify(
            $user,
            'Your MIS account was unlocked',
            'The MIS Office removed the temporary login lock from your account. You may sign in again. If you did not request help, contact the MIS Office.'
        );

        return back()->with('success', "The account for {$user->full_name} was unlocked. The user may try signing in again.");
    }

    public function sendPasswordReset(User $user)
    {
        if (! $user->is_active) {
            return back()->with('error', 'Reactivate this account before sending a password reset link.');
        }

        try {
            $status = PasswordBroker::sendResetLink(['email' => $user->email]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'The reset email could not be sent. Check the mail settings and try again.');
        }

        if ($status !== PasswordBroker::RESET_LINK_SENT) {
            return back()->with('error', __($status));
        }

        $this->audit('password_reset_invited', "A secure password-reset link was sent to {$user->username}.", [
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'A one-time password-reset link was sent to the user.');
    }

    public function forceLogout(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Use your own security settings to sign out your current administrator account.');
        }

        UserSession::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
        DB::table('sessions')->where('user_id', $user->id)->delete();
        TrustedAdminDevice::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        $this->audit('user_sessions_revoked', "All sessions for {$user->username} were revoked.", [
            'user_id' => $user->id,
        ]);
        app(SecurityNotificationService::class)->notify(
            $user,
            'All MIS sessions were signed out',
            'The MIS Office signed your account out from every device. Please sign in again.'
        );

        return back()->with('success', 'All active sessions and trusted administrator devices were revoked.');
    }

    public function requirePasswordChange(User $user)
    {
        if (! $user->is_active) {
            return back()->with('error', 'Reactivate this account before requiring a password change.');
        }

        $user->update(['must_change_password' => true]);
        $this->audit('password_change_required', "A password change was required for {$user->username}.", [
            'user_id' => $user->id,
        ]);
        app(SecurityNotificationService::class)->notify(
            $user,
            'Password change required',
            'The MIS Office requires you to change your password after your next verified sign-in.'
        );

        return back()->with('success', 'The user must change their password after the next verified sign-in.');
    }

    private function activeAdminCount(): int
    {
        return User::where('is_active', true)
            ->where(fn ($query) => $query->where('role', 'admin')->orWhere('is_admin', true))
            ->count();
    }

    private function audit(string $action, string $description, array $values = []): void
    {
        AdminActivityLog::create([
            'actor_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'new_values' => $values ?: null,
            'ip_address' => request()->ip(),
        ]);
    }

    private function clearLoginRateLimits(User $user): void
    {
        $identifiers = collect([$user->username, $user->email])
            ->filter()
            ->map(fn (string $identifier) => Str::lower(trim($identifier)))
            ->unique();
        $addresses = LoginAttempt::whereIn('username', [$user->username, $user->email])
            ->whereNotNull('ip_address')
            ->latest('created_at')
            ->limit(50)
            ->pluck('ip_address')
            ->filter()
            ->unique();

        foreach ($identifiers as $identifier) {
            foreach ($addresses as $address) {
                RateLimiter::clear("login:{$identifier}|{$address}");
            }
        }
    }

    /**
     * Get user details with login logs for modal view
     *
     * @return JsonResponse
     */
    public function getDetails($id)
    {
        try {
            Log::info('Fetching user details for ID: '.$id);

            $user = User::findOrFail($id);
            Log::info('User found: '.$user->username);

            // Get login attempts for this user - wrapped in try-catch to avoid crashing
            $logs = [];
            try {
                $loginAttempts = LoginAttempt::where('username', $user->username)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                Log::info('Found '.count($loginAttempts).' login attempts');

                $logs = $loginAttempts->map(function ($log) {
                    $createdAtFormatted = 'Unknown';
                    if (isset($log->created_at) && $log->created_at) {
                        if (is_string($log->created_at)) {
                            $createdAtFormatted = $log->created_at;
                        } else {
                            $createdAtFormatted = $log->created_at->format('M d, Y H:i A');
                        }
                    }

                    return [
                        'ip_address' => isset($log->ip_address) ? $log->ip_address : 'Unknown',
                        'device_info' => isset($log->device_info) ? $log->device_info : 'Unknown',
                        'user_agent' => isset($log->user_agent) ? $log->user_agent : 'Unknown',
                        'success' => isset($log->success) ? (bool) $log->success : false,
                        'failure_reason' => isset($log->failure_reason) ? $log->failure_reason : null,
                        'created_at' => $createdAtFormatted,
                    ];
                })->toArray();
            } catch (\Exception $logError) {
                // If logs query fails, just return empty logs array
                Log::warning('Error fetching login logs: '.$logError->getMessage());
                $logs = [];
            }

            $userData = [
                'id' => $user->id,
                'full_name' => (isset($user->first_name) ? $user->first_name : '').' '.(isset($user->last_name) ? $user->last_name : ''),
                'first_name' => isset($user->first_name) ? $user->first_name : '',
                'last_name' => isset($user->last_name) ? $user->last_name : '',
                'email' => isset($user->email) ? $user->email : '',
                'username' => isset($user->username) ? $user->username : '',
                'role' => isset($user->role) ? ucfirst($user->role) : 'User',
                'email_verified' => (isset($user->email_verified_at) && $user->email_verified_at) ? 'Yes' : 'No',
                'email_verified_at' => $this->formatDateTime($user->email_verified_at, 'Not verified', 'M d, Y H:i A'),
                'created_at' => $this->formatDateTime($user->created_at, 'Unknown', 'M d, Y'),
                'last_login' => $this->formatDateTime($user->updated_at, 'Never', 'M d, Y H:i A'),
                'is_locked' => $user->isAccountLocked(),
                'failed_login_attempts' => (int) $user->failed_login_attempts,
                'locked_until' => $this->formatDateTime($user->locked_until, null, 'M d, Y H:i A'),
            ];

            Log::info('Returning user details successfully');

            return response()->json([
                'success' => true,
                'user' => $userData,
                'logs' => $logs,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDetails: '.$e->getMessage().' - '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching user details: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper function to safely format datetime strings or objects
     */
    private function formatDateTime($dateValue, $defaultValue = 'Unknown', $format = 'M d, Y H:i A')
    {
        if (! isset($dateValue) || ! $dateValue) {
            return $defaultValue;
        }

        // If it's already a string, return it as is
        if (is_string($dateValue)) {
            return $dateValue;
        }

        // If it's a Carbon object, format it
        try {
            return $dateValue->format($format);
        } catch (\Exception $e) {
            return $defaultValue;
        }
    }
}
