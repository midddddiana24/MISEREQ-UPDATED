<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $table = 'password_history';

    protected $fillable = [
        'user_id',
        'password_hash',
    ];

    public $timestamps = false;
    public const CREATED_AT = 'changed_at';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
