<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two-Factor Authentication Security Headers Middleware
 *
 * Applies security headers specifically for 2FA verification pages,
 * preventing clickjacking while maintaining proper CSP policies.
 */
class TwoFactorAuthSecurityHeaders
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking (allows same-origin framing only, which is safe)
        $response->header('X-Frame-Options', 'SAMEORIGIN');

        // MIME type sniffing protection
        $response->header('X-Content-Type-Options', 'nosniff');

        // XSS Protection
        $response->header('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
