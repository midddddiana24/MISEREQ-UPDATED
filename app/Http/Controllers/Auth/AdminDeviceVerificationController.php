<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminDeviceChallenge;
use App\Models\UserSession;
use App\Services\AdminDeviceTrustService;
use App\Services\SecurityNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminDeviceVerificationController extends Controller
{
    public function show(Request $request, AdminDeviceTrustService $devices): View|RedirectResponse
    {
        if (!$request->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }

        $challenge = $devices->pendingChallenge($request);
        if (!(bool) $request->session()->get('admin_device_bootstrap', false) && !$challenge) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-device-verify', compact('challenge'));
    }

    public function verifyCode(Request $request, AdminDeviceTrustService $devices): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $challenge = $devices->pendingChallenge($request);

        if (!$challenge || !$challenge->isPending() || !Hash::check($request->code, $challenge->code_hash)) {
            return back()->withErrors(['code' => 'The verification code is invalid or expired.']);
        }

        $challenge->update([
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return $this->finish($request, $devices, 'This device is now trusted.');
    }

    public function continueApproved(Request $request, AdminDeviceTrustService $devices): RedirectResponse
    {
        $challenge = $devices->pendingChallenge($request);
        if (!(bool) $request->session()->get('admin_device_bootstrap', false)
            && (!$challenge || $challenge->approved_at === null)) {
            return back()->with('info', 'This sign-in is still waiting for approval.');
        }

        return $this->finish($request, $devices, 'New-device verification completed.');
    }

    public function review(Request $request, string $publicId, AdminDeviceTrustService $devices): View|RedirectResponse
    {
        $challenge = AdminDeviceChallenge::where('public_id', $publicId)->firstOrFail();
        abort_unless($challenge->user_id === $request->user()->id && $request->user()->isAdmin(), 403);

        if (!$devices->currentDevice($request->user(), $request)) {
            return redirect()->route('admin.device.verify')
                ->with('warning', 'Approve this request from a device that is already trusted.');
        }

        return view('auth.admin-device-review', compact('challenge'));
    }

    public function approve(Request $request, string $publicId, AdminDeviceTrustService $devices): RedirectResponse
    {
        $challenge = $this->reviewable($request, $publicId, $devices);
        if (!$challenge->isPending()) {
            return back()->with('info', 'This sign-in request is no longer pending.');
        }

        $challenge->update(['approved_at' => now(), 'approved_by' => $request->user()->id]);

        return back()->with('success', 'The new-device sign-in was approved.');
    }

    public function deny(Request $request, string $publicId, AdminDeviceTrustService $devices): RedirectResponse
    {
        $challenge = $this->reviewable($request, $publicId, $devices);
        if (!$challenge->isPending()) {
            return back()->with('info', 'This sign-in request is no longer pending.');
        }

        $challenge->update(['denied_at' => now()]);
        if ($challenge->session_id) {
            UserSession::where('session_id', $challenge->session_id)->update(['revoked_at' => now()]);
            DB::table('sessions')->where('id', $challenge->session_id)->delete();
        }

        app(SecurityNotificationService::class)->notify(
            $request->user(),
            'Administrator sign-in denied',
            "The sign-in from {$challenge->device_name} at IP {$challenge->ip_address} was denied."
        );

        return redirect()->route('admin.dashboard')->with('success', 'The new-device sign-in was denied.');
    }

    private function finish(
        Request $request,
        AdminDeviceTrustService $devices,
        string $message
    ): RedirectResponse {
        $cookie = $devices->trustCurrentDevice($request->user(), $request);
        app(SecurityNotificationService::class)->notify(
            $request->user(),
            'Administrator device trusted',
            'A device was approved for administrator access: '
                . $devices->deviceName($request->userAgent())
                . ' at IP ' . $request->ip() . '.'
        );

        return redirect()->route('admin.dashboard')->with('success', $message)->withCookie($cookie);
    }

    private function reviewable(
        Request $request,
        string $publicId,
        AdminDeviceTrustService $devices
    ): AdminDeviceChallenge {
        $challenge = AdminDeviceChallenge::where('public_id', $publicId)->firstOrFail();
        abort_unless(
            $challenge->user_id === $request->user()->id
            && $request->user()->isAdmin()
            && $devices->currentDevice($request->user(), $request),
            403
        );

        return $challenge;
    }
}
