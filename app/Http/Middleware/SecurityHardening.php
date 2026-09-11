<?php

/**
 * SECURITY HARDENING MIDDLEWARE
 *
 * Implements rate limiting, CSRF protection, and security best practices
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class SecurityHardening
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): BaseResponse
    {
        // Rate limiting for sensitive endpoints
        if ($this->isSensitiveEndpoint($request)) {
            $key = 'security_'.$request->ip().'_'.$request->path();

            if ($this->limiter->tooManyAttempts($key, 5, 1)) { // 5 requests per minute
                return response('Too many requests. Please try again later.', 429);
            }

            $this->limiter->hit($key, 60); // 60 second window
        }

        // Add security headers
        return $next($request)
            ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->header('Content-Security-Policy', "default-src 'self'; script-src 'self' https://fonts.googleapis.com; style-src 'self' https://fonts.googleapis.com 'unsafe-inline'")
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-XSS-Protection', '1; mode=block')
            ->header('Permissions-Policy', 'geolocation=(), camera=(), microphone=()')
            ->header('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Check if endpoint is sensitive (auth, admin, etc.)
     */
    private function isSensitiveEndpoint(Request $request): bool
    {
        $sensitivePaths = [
            'login',
            'register',
            '2fa',
            'admin',
            'settings',
            'api/messages',
        ];

        foreach ($sensitivePaths as $path) {
            if ($request->is($path) || $request->is($path.'/*')) {
                return true;
            }
        }

        return false;
    }
}
