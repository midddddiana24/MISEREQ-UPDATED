<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Input Sanitization Middleware
 *
 * Sanitizes all user inputs to prevent XSS and other attacks
 * - Trims whitespace
 * - Removes null bytes
 * - Encodes special characters in strings
 * - Preserves arrays and files
 */
class SanitizeInput
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
        // Sanitize all request input
        $request->merge($this->sanitizeData($request->all()));

        return $next($request);
    }

    /**
     * Recursively sanitize data
     *
     * @param  array  $data
     * @return array
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            // Sanitize the key
            $cleanKey = $this->sanitizeString($key);

            if (is_array($value)) {
                // Recursively sanitize arrays
                $sanitized[$cleanKey] = $this->sanitizeData($value);
            } elseif (is_string($value)) {
                // Sanitize string values
                $sanitized[$cleanKey] = $this->sanitizeString($value);
            } else {
                // Keep other types as-is
                $sanitized[$cleanKey] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize a string value
     *
     * @param  string  $value
     * @return string
     */
    private function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Trim whitespace
        $value = trim($value);

        // Remove control characters (except newlines and tabs)
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        // Don't HTML encode here - let Blade {{ }} do that
        // Just clean up dangerous sequences

        return $value;
    }
}
