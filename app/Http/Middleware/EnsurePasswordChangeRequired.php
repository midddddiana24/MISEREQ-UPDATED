<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChangeRequired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user?->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('password.required*', '2fa.*', 'admin.device.*', 'logout')) {
            return $next($request);
        }

        if ($user->isTwoFactorEnabled() && ! (bool) $request->session()->get('2fa_verified', false)) {
            return $next($request);
        }

        return redirect()->route('password.required')
            ->with('warning', 'Change your password before continuing.');
    }
}
