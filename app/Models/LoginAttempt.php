<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = [
        'username',
        'ip_address',
        'success',
        'failure_reason',
        'user_agent',
        'device_info',
    ];

    protected $casts = [
        'success' => 'boolean',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public const CREATED_AT = 'created_at';

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
