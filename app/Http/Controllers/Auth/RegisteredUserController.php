<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Rules\StrongPassword;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show register form
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle Registration
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:3'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'office' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'confirmed', new StrongPassword],
        ]);

        $hashedPassword = Hash::make($request->password);
        $user = User::create([
            'first_name' => $request->first_name,
            'middle_initial' => $request->middle_initial,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'office' => $request->office,
            'username' => $request->username,
            'password' => $hashedPassword,
            'full_name' => trim($request->first_name.' '.($request->middle_initial ? $request->middle_initial.'. ' : '').$request->last_name),
            'role' => 'user',
            'is_admin' => false,
            'is_active' => true,
            'password_expires_at' => Carbon::now()->addDays(90),
        ]);

        PasswordHistory::create(['user_id' => $user->id, 'password_hash' => $hashedPassword]);

        event(new Registered($user));

        Auth::login($user);

        // Regenerate session to prevent session fixation attacks (same as login)
        $request->session()->regenerate();

        // Mark 2FA as verified - new users don't have 2FA enabled by default
        // This prevents redirect to 2fa.verify on first login after signup
        $request->session()->put('2fa_verified', true);

        return redirect('/dashboard');
    }
}
