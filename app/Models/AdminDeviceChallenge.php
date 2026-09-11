<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminDeviceChallenge extends Model
{
    protected $fillable = [
        'public_id',
        'user_id',
        'session_id',
        'code_hash',
        'device_name',
        'ip_address',
        'user_agent',
        'expires_at',
        'approved_at',
        'denied_at',
        'approved_by',
    ];

    protected $hidden = ['code_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'denied_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->approved_at === null
            && $this->denied_at === null
            && $this->expires_at->isFuture();
    }
}
