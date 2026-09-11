<?php

namespace App\Http\Controllers\Auth;

use App\Models\PasswordHistory;
use App\Models\UserSession;
use App\Rules\NotInPasswordHistory;
use App\Rules\StrongPassword;
use App\Services\SecurityNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController
{
    /**
     * Update the user's password.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', new StrongPassword(), new NotInPasswordHistory($request->user()->id)],
        ]);

        $hashedPassword = Hash::make($validated['password']);
        $request->user()->update([
            'password' => $hashedPassword,
            'password_expires_at' => Carbon::now()->addDays(90),
            'must_change_password' => false,
        ]);
        PasswordHistory::create(['user_id' => $request->user()->id, 'password_hash' => $hashedPassword]);
        UserSession::where('user_id', $request->user()->id)
            ->where('session_id', '!=', $request->session()->getId())
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
        app(SecurityNotificationService::class)->notify(
            $request->user(),
            'Your MIS password was changed',
            'Your password was changed and other active sessions were signed out. If this was not you, contact the MIS Office immediately.'
        );
        $request->session()->forget('auth.password_confirmed_at');

        return back()->with('status', 'password-updated');
    }
}
