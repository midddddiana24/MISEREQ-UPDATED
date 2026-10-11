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
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent XSS attacks
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content Security Policy - Prevent inline scripts
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; ".
                "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; ".
                "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com; ".
                "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net; ".
                "img-src 'self' data: https:; ".
                "frame-ancestors 'none'; ".
                "base-uri 'self'; ".
                "form-action 'self'"
        );

        // Referrer policy - Don't leak user data
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Feature policy - Disable unnecessary features
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=()'
        );

        // Browsers only honor HSTS over an HTTPS response.
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
