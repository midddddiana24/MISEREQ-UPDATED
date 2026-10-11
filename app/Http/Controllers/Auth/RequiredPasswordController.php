<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordHistory;
use App\Models\UserSession;
use App\Rules\NotInPasswordHistory;
use App\Rules\StrongPassword;
use App\Services\SecurityNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RequiredPasswordController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        if (! $request->user()->must_change_password) {
            return redirect()->route($request->user()->isAdmin() ? 'admin.dashboard' : 'dashboard');
        }

        return view('auth.required-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                new StrongPassword,
                new NotInPasswordHistory($request->user()->id),
            ],
        ]);

        $hash = Hash::make($validated['password']);
        $request->user()->forceFill([
            'password' => $hash,
            'password_expires_at' => now()->addDays(90),
            'must_change_password' => false,
        ])->save();
        PasswordHistory::create(['user_id' => $request->user()->id, 'password_hash' => $hash]);
        UserSession::where('user_id', $request->user()->id)
            ->where('session_id', '!=', $request->session()->getId())
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
        app(SecurityNotificationService::class)->notifyMandatory(
            SecurityNotificationService::EVENT_PASSWORD_CHANGED,
            $request->user(),
            'Required password change completed',
            'Your password was changed and other active sessions were signed out.'
        );

        return redirect()->route($request->user()->isAdmin() ? 'admin.dashboard' : 'dashboard')
            ->with('success', 'Password changed successfully.');
    }
}
