<?php

namespace App\Http\Middleware;

use App\Services\AdminDeviceTrustService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminDeviceVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user?->isAdmin()) {
            return $next($request);
        }

        if ($request->routeIs('2fa.*', 'admin.device.*', 'logout')) {
            return $next($request);
        }

        if ($user->isTwoFactorEnabled() && ! (bool) $request->session()->get('2fa_verified', false)) {
            return $next($request);
        }

        $service = app(AdminDeviceTrustService::class);
        if ((bool) $request->session()->get('admin_device_bootstrap', false)) {
            return redirect()->route('admin.device.verify');
        }

        $challenge = $service->pendingChallenge($request);
        if (! $challenge) {
            return $next($request);
        }

        if ($challenge->denied_at || $challenge->expires_at->isPast()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['username' => $challenge->denied_at
                    ? 'This new-device sign-in was denied.'
                    : 'The new-device verification expired. Please sign in again.']);
        }

        return redirect()->route('admin.device.verify');
    }
}
