<?php

namespace App\Services;

use App\Models\AdminDeviceChallenge;
use App\Models\TrustedAdminDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class AdminDeviceTrustService
{
    public const COOKIE_NAME = 'mis_admin_device';

    public function beginLogin(User $user, Request $request): ?AdminDeviceChallenge
    {
        if (! $user->isAdmin()) {
            return null;
        }

        if ($this->currentDevice($user, $request)) {
            return null;
        }

        if (! $user->trustedAdminDevices()->whereNull('revoked_at')->exists()) {
            $request->session()->put('admin_device_bootstrap', true);

            return null;
        }

        $code = (string) random_int(100000, 999999);
        $challenge = $user->adminDeviceChallenges()->create([
            'public_id' => (string) Str::uuid(),
            'session_id' => $request->session()->getId(),
            'code_hash' => Hash::make($code),
            'device_name' => $this->deviceName($request->userAgent()),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addMinutes(15),
        ]);

        $request->session()->put('admin_device_challenge_id', $challenge->id);
        app(SecurityNotificationService::class)->notify(
            $user,
            'Approve a new administrator sign-in',
            "A sign-in from {$challenge->device_name} at IP {$challenge->ip_address} is waiting for approval. Review it from a trusted device.",
            route('admin.device.review', $challenge->public_id),
            "A sign-in from {$challenge->device_name} at IP {$challenge->ip_address} is waiting for approval. Verification code: {$code}. The code expires in 15 minutes.",
            true
        );

        return $challenge;
    }

    public function currentDevice(User $user, Request $request): ?TrustedAdminDevice
    {
        $token = (string) $request->cookie(self::COOKIE_NAME, '');
        if ($token === '') {
            return null;
        }

        $device = $user->trustedAdminDevices()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->first();

        if ($device) {
            $device->update([
                'last_used_at' => now(),
                'ip_address' => $request->ip(),
            ]);
        }

        return $device;
    }

    public function trustCurrentDevice(User $user, Request $request): Cookie
    {
        $token = Str::random(80);
        $user->trustedAdminDevices()->create([
            'token_hash' => hash('sha256', $token),
            'device_name' => $this->deviceName($request->userAgent()),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'trusted_at' => now(),
            'last_used_at' => now(),
        ]);

        $request->session()->forget(['admin_device_bootstrap', 'admin_device_challenge_id']);

        return cookie(
            self::COOKIE_NAME,
            $token,
            60 * 24 * 30,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'strict'
        );
    }

    public function pendingChallenge(Request $request): ?AdminDeviceChallenge
    {
        $id = $request->session()->get('admin_device_challenge_id');

        return $id ? AdminDeviceChallenge::find($id) : null;
    }

    public function updateChallengeSession(Request $request): void
    {
        $challenge = $this->pendingChallenge($request);
        if ($challenge) {
            $challenge->update(['session_id' => $request->session()->getId()]);
        }
    }

    public function deviceName(?string $userAgent): string
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
