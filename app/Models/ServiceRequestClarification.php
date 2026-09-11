<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequestClarification extends Model
{
    protected $fillable = [
        'service_request_id', 'asked_by', 'question', 'answered_by', 'answer', 'answered_at',
    ];

    protected $casts = ['answered_at' => 'datetime'];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function askedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asked_by');
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClarificationAttachment::class);
    }
}
