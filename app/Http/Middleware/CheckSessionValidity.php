<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionValidity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user->last_activity || $user->last_activity->lt(now()->subMinutes(5))) {
                $user->updateLastActivity($request->ip(), $request->userAgent());
            }

            // Check if password is expired (warn user)
            if ($user->isPasswordExpired()) {
                $allowedRoutes = [
                    'user.profile', 'user.settings', 'settings.updatePassword', 'user.password.update', 'password.update',
                    'logout', 'verification.notice', 'verification.send', 'verification.verify',
                    '2fa.setup', '2fa.setup.store', '2fa.verify', '2fa.verify.store',
                    'admin.device.verify', 'admin.device.verify-code', 'admin.device.continue',
                    'admin.device.review', 'admin.device.approve', 'admin.device.deny',
                    'password.required', 'password.required.update',
                ];
                if (in_array($request->route()?->getName(), $allowedRoutes, true)) {
                    return $next($request);
                }

                return redirect()->route('user.profile')
                    ->with('warning', 'Your password has expired. Please update it.');
            }
        }

        return $next($request);
    }
}
