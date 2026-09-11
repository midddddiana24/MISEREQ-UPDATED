<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class CheckTwoFactorAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only check 2FA for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $currentRoute = $request->route()?->getName() ?? '';

        $twoFactorRequired = $user?->isAdmin()
            ? Setting::get('require_2fa_admins', false)
            : Setting::get('require_2fa_clients', false);

        if ($twoFactorRequired && !$user->isTwoFactorEnabled()) {
            if (in_array($currentRoute, ['2fa.setup', '2fa.setup.store', 'logout'], true)) {
                return $next($request);
            }

            return redirect()->route('2fa.setup')
                ->with('warning', 'Two-factor authentication is required for this account.');
        }

        // If user doesn't have 2FA enabled at all, allow access
        if (!$user?->isTwoFactorEnabled()) {
            // Mark as verified and allow
            session(['2fa_verified' => true]);
            return $next($request);
        }

        // User HAS 2FA enabled - check if they've verified it in this session
        $isVerified = session('2fa_verified', false);
        if ($isVerified) {
            return $next($request);
        }

        // Get current route name
        // Allow 2FA-specific routes
        if (in_array($currentRoute, ['2fa.verify', '2fa.verify.store', 'logout'], true)) {
            return $next($request);
        }

        // Allow API routes
        if (str_starts_with($currentRoute, 'api.')) {
            return $next($request);
        }

        // User has 2FA enabled but hasn't verified yet - redirect to 2FA
        return redirect()->route('2fa.verify')
            ->with('warning', '🔒 Please verify your 2FA code to continue.');
    }
}
