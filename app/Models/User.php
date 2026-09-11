<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;

/**
 * @method bool isTwoFactorEnabled()
 * @method void enable2FA(string $secret)
 * @method void disable2FA()
 * @method bool isAccountLocked()
 * @method void incrementFailedLoginAttempts()
 * @method void resetFailedLoginAttempts()
 * @method bool isPasswordExpired()
 * @method int getPasswordAge()
 * @method void updateLastActivity(?string $ip = null, ?string $userAgent = null)
 * @method bool isSessionValid(string $ip, string $userAgent)
 * @method bool isAdmin()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany serviceRequests()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany sentMessages()
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'middle_initial',
        'last_name',
        'office',
        'username',
        'email',
        'password',
        'designation',
        'phone_local',
        'is_admin',
        'role',
        'is_active',
        'deactivated_at',
        'deactivated_by',
        'google2fa_secret',
        'two_factor_recovery_code',
        'two_fa_enabled_at',
        'password_expires_at',
        'must_change_password',
        'last_activity',
        'session_ip',
        'session_user_agent',
        'notify_email_enabled',
        'notify_request_reminders',
        'notify_system_announcements',
        'notify_request_updates',
        'notify_security_alerts',
        'theme',
        'language',
        'email_verified',
        'failed_login_attempts',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
        'two_factor_recovery_code',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
        'two_fa_enabled_at' => 'datetime',
        'password_expires_at' => 'datetime',
        'must_change_password' => 'boolean',
        'last_activity' => 'datetime',
        'locked_until' => 'datetime',
        'notify_email_enabled' => 'boolean',
        'notify_request_reminders' => 'boolean',
        'notify_system_announcements' => 'boolean',
        'notify_request_updates' => 'boolean',
        'notify_security_alerts' => 'boolean',
        'email_verified' => 'boolean',
        'google2fa_secret' => 'encrypted',
        'two_factor_recovery_code' => 'encrypted:array',
    ];

    // Add accessor for full name
    public function getFullNameAttribute(): string
    {
        $middle = $this->middle_initial ? $this->middle_initial . '. ' : '';
        return $this->first_name . ' ' . $middle . $this->last_name;
    }

    // Helper method
    public function isAdmin(): bool
    {
        return $this->is_admin === true || $this->role === 'admin';
    }

    // 2FA Methods
    public function isTwoFactorEnabled(): bool
    {
        return $this->two_fa_enabled_at !== null;
    }

    public function enable2FA(string $secret): void
    {
        $this->google2fa_secret = $secret;
        $this->two_fa_enabled_at = now();
        $this->save();
    }

    public function disable2FA(): void
    {
        $this->google2fa_secret = null;
        $this->two_factor_recovery_code = null;
        $this->two_fa_enabled_at = null;
        $this->save();
    }

    // Account Lockout Methods
    public function isAccountLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function incrementFailedLoginAttempts(): void
    {
        $this->failed_login_attempts++;

        // Lock account after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->locked_until = now()->addMinutes(30);
        }

        $this->save();
    }

    public function resetFailedLoginAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }

    // Password Management Methods
    public function isPasswordExpired(): bool
    {
        if (!$this->password_expires_at) {
            return false;
        }
        return $this->password_expires_at->isPast();
    }

    public function getPasswordAge(): int
    {
        return $this->created_at->diffInDays(now());
    }

    // Session Management Methods
    public function updateLastActivity(?string $ip = null, ?string $userAgent = null): void
    {
        $this->last_activity = now();
        if ($ip) {
            $this->session_ip = $ip;
        }
        if ($userAgent) {
            $this->session_user_agent = $userAgent;
        }
        $this->save();
    }

    public function isSessionValid(string $ip, string $userAgent): bool
    {
        if ($this->session_ip && $this->session_ip !== $ip) {
            return false;
        }

        if ($this->session_user_agent && $this->session_user_agent !== $userAgent) {
            return false;
        }

        return true;
    }

    // Relationships
    public function serviceRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function sentMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function sessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function trustedAdminDevices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrustedAdminDevice::class);
    }

    public function adminDeviceChallenges(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AdminDeviceChallenge::class);
    }

    public function passwordHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function loginAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoginAttempt::class, 'username', 'username');
    }
}
