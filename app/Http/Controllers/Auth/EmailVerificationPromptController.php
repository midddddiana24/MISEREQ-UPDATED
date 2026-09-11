<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

class EmailVerificationPromptController
{
    /**
     * Show the email verification prompt.
     */
    public function __invoke(Request $request)
    {
        if (! $request->user()->hasVerifiedEmail()) {
            return view('auth.verify-email');
        }

        if ($request->user()->isAdmin()) {
            $request->session()->forget('url.intended');

            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }
}
