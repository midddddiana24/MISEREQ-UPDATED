<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'sender_id',
        'message',
        'is_admin',
        'read_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the service request associated with this message
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    /**
     * Get the user who sent this message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if message is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Scope to get unread messages
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to get messages for a specific request
     */
    public function scopeForRequest(Builder $query, int $requestId): Builder
    {
        return $query->where('service_request_id', $requestId);
    }

    /**
     * Scope to get messages sent by admins
     */
    public function scopeFromAdmin(Builder $query): Builder
    {
        return $query->where('is_admin', true);
    }

    /**
     * Scope to get messages sent by users
     */
    public function scopeFromUser(Builder $query): Builder
    {
        return $query->where('is_admin', false);
    }
}
