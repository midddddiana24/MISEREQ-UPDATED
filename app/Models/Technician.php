<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technician extends Model
{
    public const JOB_ASSIGNMENTS = [
        'Network Administrator',
        'Technical Staff',
        'Web Administrator',
        'Computer Programmer',
    ];

    protected $fillable = [
        'user_id',
        'email',
        'first_name',
        'last_name',
        'specialization',
        'job_assignment',
        'status',
        'is_active',
        'deactivated_at',
        'deactivated_by',
        'current_workload',
        'skills',
        'phone',
        'notes',
    ];

    public static function normalizeJobAssignment(?string $assignment, ?string $specialization = null): ?string
    {
        $value = strtolower(trim($assignment ?: $specialization ?: ''));

        return match (true) {
            str_contains($value, 'network') => 'Network Administrator',
            str_contains($value, 'web') => 'Web Administrator',
            str_contains($value, 'programmer'), str_contains($value, 'developer'), str_contains($value, 'software') => 'Computer Programmer',
            str_contains($value, 'technical'), str_contains($value, 'technician'), str_contains($value, 'support') => 'Technical Staff',
            default => null,
        };
    }

    protected $casts = [
        'skills' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    /**
     * Get the user associated with this technician
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all service requests assigned to this technician
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'assigned_technician_id');
    }

    /**
     * Get active assignments (pending or processing only)
     */
    public function activeAssignments(): HasMany
    {
        return $this->serviceRequests()
            ->whereIn('status', ['pending', 'processing', 'awaiting_requester_info', 'awaiting_confirmation', 'reopened', 'escalated']);
    }

    /**
     * Check if technician is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->current_workload < 10;
    }

    /**
     * Update workload count
     */
    public function updateWorkload(): void
    {
        $this->current_workload = $this->activeAssignments()->count();
        if ($this->current_workload >= 10) {
            $this->status = 'busy';
        } elseif ($this->status === 'busy' && $this->current_workload < 5) {
            $this->status = 'available';
        }
        $this->save();
    }

    /**
     * Get technician full name
     */
    public function getFullNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->full_name ?? ($this->user->first_name.' '.$this->user->last_name);
        }

        return ($this->first_name ?? '').' '.($this->last_name ?? '');
    }

    /**
     * Get workload percentage
     */
    public function getWorkloadPercentageAttribute(): float
    {
        return ($this->current_workload / 10) * 100;
    }
}
