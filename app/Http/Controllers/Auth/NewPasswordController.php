<?php

namespace App\Http\Controllers\Auth;

use App\Models\PasswordHistory;
use App\Models\User;
use App\Models\UserSession;
use App\Rules\NotInPasswordHistory;
use App\Rules\StrongPassword;
use App\Services\SecurityNotificationService;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewPasswordController
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request)
    {
        $resetUser = User::where('email', $request->email)->first();
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', new StrongPassword, new NotInPasswordHistory($resetUser?->id)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $hashedPassword = Hash::make($password);
                $user->forceFill([
                    'password' => $hashedPassword,
                    'password_expires_at' => Carbon::now()->addDays(90),
                    'must_change_password' => false,
                ])->setRememberToken(Str::random(60));

                $user->save();
                PasswordHistory::create(['user_id' => $user->id, 'password_hash' => $hashedPassword]);
                UserSession::where('user_id', $user->id)
                    ->whereNull('revoked_at')
                    ->update(['revoked_at' => now()]);
                app(SecurityNotificationService::class)->notify(
                    $user,
                    'Your MIS password was reset',
                    'Your password was reset and all active sessions were signed out. If this was not you, contact the MIS Office immediately.'
                );

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
