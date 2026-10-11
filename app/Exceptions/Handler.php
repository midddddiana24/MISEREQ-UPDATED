<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 419 || ! $request->is('login')) {
                return null;
            }

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->with(
                'warning',
                'Your sign-in page expired and was refreshed. Please enter your password again.'
            );
        });

        $this->renderable(function (ThrottleRequestsException $exception, Request $request) {
            $retryAfter = (int) ($exception->getHeaders()['Retry-After'] ?? 60);

            if ($request->isMethod('post') && $request->is('login') && ! $request->expectsJson()) {
                return back()->withErrors([
                    'username' => 'Too many sign-in attempts. Try again in about '.max(1, $retryAfter).' seconds, or use Forgot password if you need help.',
                ])->onlyInput('username')->with('openLogin', true);
            }

            if (! $request->expectsJson()) {
                return null;
            }

            return new JsonResponse([
                'success' => false,
                'message' => 'Please wait a moment before trying again.',
                'error' => 'Too many attempts. Your information was kept so you can retry shortly.',
                'retry_after' => $retryAfter,
            ], 429, $exception->getHeaders());
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
