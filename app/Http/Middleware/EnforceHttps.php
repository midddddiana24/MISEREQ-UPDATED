<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.force_https', false) || $request->isSecure()) {
            return $next($request);
        }

        $configuredBase = rtrim((string) config('app.url'), '/');
        $base = str_starts_with($configuredBase, 'https://')
            ? $configuredBase
            : 'https://'.$request->getHost();
        $status = in_array($request->method(), ['GET', 'HEAD'], true) ? 301 : 307;

        return redirect()->away($base.$request->getRequestUri(), $status);
    }
}
