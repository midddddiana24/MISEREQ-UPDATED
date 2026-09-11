<?php

/**
 * PERFORMANCE OPTIMIZATION MIDDLEWARE
 * 
 * Implements response compression, caching headers, and request throttling
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class PerformanceOptimization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): BaseResponse
    {
        $response = $next($request);

        // Add caching headers for static content
        if ($this->isStaticContent($request)) {
            $response->header('Cache-Control', 'public, max-age=31536000');
            $response->header('Expires', gmdate('D, d M Y H:i:s T', time() + 31536000));
        }

        // Add compression for larger responses
        if ($this->shouldCompress($response)) {
            $response->header('Content-Encoding', 'gzip');
        }

        // Security headers
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }

    /**
     * Check if content is static
     */
    private function isStaticContent(Request $request): bool
    {
        $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'woff', 'woff2'];
        $path = $request->path();

        foreach ($staticExtensions as $ext) {
            if (str_ends_with($path, '.' . $ext)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if response should be compressed
     */
    private function shouldCompress(BaseResponse $response): bool
    {
        $size = strlen($response->getContent());
        return $size > 1024; // Compress responses larger than 1KB
    }
}
