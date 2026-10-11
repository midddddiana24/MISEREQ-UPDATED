<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        $user = $request->user();
        $record = UserSession::where('session_id', $sessionId)->first();
        $serverLifetime = max(15, (int) config('session.lifetime', 120));
        $idleMinutes = $user->isAdmin()
            ? $serverLifetime
            : min($serverLifetime, max(15, min(240, (int) Setting::get('client_session_timeout', 120))));

        if ($record && (
            $record->revoked_at !== null
            || $record->user_id !== $user->id
            || $record->last_activity_at->lt(now()->subMinutes($idleMinutes))
        )) {
            $record->revoke();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('warning', 'Your session ended for security. Please sign in again.');
        }

        if (! $record) {
            $record = UserSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_name' => $this->deviceName($request->userAgent()),
                'last_activity_at' => now(),
            ]);

            if (! $user->isAdmin()) {
                $this->enforceSessionLimit($record);
            }
        } elseif ($record->last_activity_at->lt(now()->subMinute())) {
            $record->update([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_name' => $this->deviceName($request->userAgent()),
                'last_activity_at' => now(),
            ]);
        }

        return $next($request);
    }

    private function enforceSessionLimit(UserSession $current): void
    {
        $maximum = max(1, min(10, (int) Setting::get('max_active_client_sessions', 3)));
        $excess = UserSession::where('user_id', $current->user_id)
            ->whereNull('revoked_at')
            ->where('id', '!=', $current->id)
            ->orderByDesc('last_activity_at')
            ->skip(max(0, $maximum - 1))
            ->take(20)
            ->get();

        foreach ($excess as $session) {
            $session->revoke();
        }
    }

    private function deviceName(?string $userAgent): string
    {
        $agent = strtolower((string) $userAgent);
        $browser = str_contains($agent, 'edg/') ? 'Edge'
            : (str_contains($agent, 'firefox/') ? 'Firefox'
            : (str_contains($agent, 'chrome/') ? 'Chrome'
            : (str_contains($agent, 'safari/') ? 'Safari' : 'Browser')));
        $platform = str_contains($agent, 'android') ? 'Android'
            : (str_contains($agent, 'iphone') || str_contains($agent, 'ipad') ? 'iOS'
            : (str_contains($agent, 'windows') ? 'Windows'
            : (str_contains($agent, 'macintosh') ? 'macOS'
            : (str_contains($agent, 'linux') ? 'Linux' : 'Unknown device'))));

        return "{$browser} on {$platform}";
    }
}
