<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Content Security Policy Middleware
 *
 * Sets security headers to prevent:
 * - Cross-Site Scripting (XSS)
 * - Clickjacking
 * - MIME type sniffing
 * - Referrer leaks
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent XSS attacks
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', '1; mode=block');

        // Content Security Policy - Prevent inline scripts
        $response->header(
            'Content-Security-Policy',
            "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; " .
                "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com; " .
                "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net; " .
                "img-src 'self' data: https:; " .
                "frame-ancestors 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self'"
        );

        // Referrer policy - Don't leak user data
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Feature policy - Disable unnecessary features
        $response->header(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=()'
        );

        // Browsers only honor HSTS over an HTTPS response.
        if ($request->isSecure()) {
            $response->header(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
