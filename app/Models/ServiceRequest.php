<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'submitted',
        'pending',
        'processing',
        'awaiting_requester_info',
        'awaiting_confirmation',
        'completed',
        'closed',
        'reopened',
        'escalated',
        'cancelled',
        'rejected',
    ];

    public const OPEN_STATUSES = [
        'submitted',
        'pending',
        'processing',
        'awaiting_requester_info',
        'awaiting_confirmation',
        'reopened',
        'escalated',
    ];

    protected $fillable = [
        'control_number',
        'user_id',
        'requester_name',
        'requester_designation',
        'unit_head_name',
        'requester_office',
        'office',
        'phone',
        'services',
        'description',
        'title',
        'category',
        'priority',
        'requested_urgency',
        'attachment',
        'property_no',
        'status',
        'created_by',
        'is_admin_created',
        'status_before_clarification',
        'assigned_to',
        'assigned_technician_id',
        'assigned_by',
        'assigned_date',
        'expected_completion_at',
        'expected_completion_change_reason',
        'sla_paused_at',
        'sla_total_paused_seconds',
        'technician_notes',
        'client_update',
        'client_update_at',
        'client_update_by',
        'scheduled_visit_at',
        'scheduled_location',
        'schedule_response',
        'schedule_response_note',
        'schedule_responded_at',
        'responded_by',
        'date_responded',
        'date_completed',
        'work_completed_at',
        'resolution_confirmed_at',
        'closed_at',
        'archived_at',
        'archived_by',
        'archive_reason',
        'reopened_at',
        'escalated_at',
        'escalation_reason',
        'comments',
        'cancellation_reason',
        'feedback_rating',
        'feedback_comment',
        'feedback_resolved',
        'feedback_submitted_at',
        'feedback_follow_up_status',
        'feedback_follow_up_notes',
        'feedback_reviewed_by',
        'feedback_reviewed_at',
        'requestor_name',
        'department',
        'office_location',
        'contact_number',
        'phone_local_no',
        'date_of_request',
        'time_of_request',
        'network_new_computer',
        'network_new_printer',
        'network_data_request',
        'network_new_telephone',
        'network_change_telephone',
        'network_move_location',
        'network_others',
        'network_services',
        'ict_troubleshoot_pc',
        'ict_troubleshoot_printer',
        'ict_troubleshoot_telephone',
        'ict_hardware_install',
        'ict_software_install',
        'ict_equipment_services',
        'ict_procurement',
        'ict_others',
        'ict_property_no',
        'mis_new_user',
        'mis_reset_password',
        'mis_software_problem',
        'mis_data_repair',
        'mis_request_copy_data',
        'mis_grant_privilege',
        'mis_software_services',
        'mis_others',
        'web_new_email',
        'web_reset_email',
        'web_tarpaulin',
        'web_photo_video',
        'web_website_update',
        'web_request_multimedia',
        'web_admin_services',
        'web_others',
        'prr_no',
        'major_repair',
        'mis_director_signature',
        'mis_head_signature',
        'end_user_signature',
        'acceptance_date',
        'end_user_date',
    ];

    protected $casts = [
        'services' => 'json',
        'network_services' => 'json',
        'ict_equipment_services' => 'json',
        'mis_software_services' => 'json',
        'web_admin_services' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date_responded' => 'datetime',
        'date_completed' => 'datetime',
        'work_completed_at' => 'datetime',
        'resolution_confirmed_at' => 'datetime',
        'closed_at' => 'datetime',
        'archived_at' => 'datetime',
        'reopened_at' => 'datetime',
        'escalated_at' => 'datetime',
        'assigned_date' => 'datetime',
        'expected_completion_at' => 'datetime',
        'sla_paused_at' => 'datetime',
        'sla_total_paused_seconds' => 'integer',
        'client_update_at' => 'datetime',
        'scheduled_visit_at' => 'datetime',
        'schedule_responded_at' => 'datetime',
        'feedback_resolved' => 'boolean',
        'feedback_submitted_at' => 'datetime',
        'feedback_reviewed_at' => 'datetime',
        'date_of_request' => 'date',
        'end_user_date' => 'date',
        'acceptance_date' => 'datetime',
    ];

    /**
     * Override setAttribute to prevent temporary attributes (starting with _) from being persisted
     */
    public function setAttribute($key, $value)
    {
        // Temporary attributes (starting with _) are used by observers/controllers but should not be saved to DB
        if (str_starts_with($key, '_')) {
            // Store in a temporary property instead of model attributes
            if (!isset($this->_temporaryData)) {
                $this->_temporaryData = [];
            }
            $this->_temporaryData[$key] = $value;
            return $this;
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Override getAttribute to retrieve temporary attributes
     */
    public function getAttribute($key)
    {
        if (str_starts_with($key, '_') && isset($this->_temporaryData[$key])) {
            return $this->_temporaryData[$key];
        }
        return parent::getAttribute($key);
    }

    /**
     * Get the user who created this request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assigned technician
     */
    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'assigned_technician_id');
    }

    /**
     * Get status logs
     */
    public function statusLogs()
    {
        return $this->hasMany(RequestStatusLog::class, 'service_request_id');
    }

    /**
     * Get messages for this request
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'service_request_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class)->latest();
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IctIncidentReport::class, 'service_request_id');
    }

    public function preventiveMaintenanceChecklists(): HasMany
    {
        return $this->hasMany(PreventiveMaintenanceChecklist::class, 'service_request_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ServiceRequestAttachment::class);
    }

    public function clarifications(): HasMany
    {
        return $this->hasMany(ServiceRequestClarification::class)->latest();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function clientUpdateAuthor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_update_by');
    }

    /**
     * Get the primary service type
     */
    public function getServiceTypeAttribute()
    {
        if (is_array($this->services) && count($this->services) > 0) {
            return $this->services[0] ?? 'Unknown';
        }

        // Fallback to checking individual service fields
        if ($this->network_new_computer || $this->network_new_printer || $this->network_data_request || $this->network_new_telephone || $this->network_change_telephone || $this->network_move_location || $this->network_others) {
            return 'Network';
        }
        if ($this->ict_troubleshoot_pc || $this->ict_troubleshoot_printer || $this->ict_troubleshoot_telephone || $this->ict_hardware_install || $this->ict_software_install || $this->ict_procurement || $this->ict_others) {
            return 'ICT Equipment';
        }
        if ($this->mis_new_user || $this->mis_reset_password || $this->mis_software_problem || $this->mis_data_repair || $this->mis_request_copy_data || $this->mis_grant_privilege || $this->mis_others) {
            return 'Software';
        }
        if ($this->web_new_email || $this->web_reset_email || $this->web_tarpaulin || $this->web_photo_video || $this->web_website_update || $this->web_request_multimedia || $this->web_others) {
            return 'Web Admin';
        }

        return 'General';
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'submitted' => 'Submitted',
            'pending' => 'Pending',
            'processing' => 'Processing',
            'awaiting_requester_info' => 'Response Required',
            'awaiting_confirmation' => 'Awaiting Confirmation',
            'completed' => 'Completed',
            'closed' => 'Closed',
            'reopened' => 'Reopened',
            'escalated' => 'Escalated',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Check if has network services
     */
    public function getNetworkServicesAttribute()
    {
        return $this->network_new_computer || $this->network_new_printer || $this->network_data_request ||
            $this->network_new_telephone || $this->network_change_telephone ||
            $this->network_move_location || $this->network_others ||
            (is_array($this->services) && in_array('Network', $this->services));
    }

    /**
     * Check if has ICT services
     */
    public function getIctEquipmentServicesAttribute()
    {
        return $this->ict_troubleshoot_pc || $this->ict_troubleshoot_printer || $this->ict_troubleshoot_telephone ||
            $this->ict_hardware_install || $this->ict_software_install || $this->ict_procurement || $this->ict_others ||
            (is_array($this->services) && in_array('ICT Equipment', $this->services));
    }

    /**
     * Check if has MIS/Software services
     */
    public function getMisSoftwareServicesAttribute()
    {
        return $this->mis_new_user || $this->mis_reset_password || $this->mis_software_problem ||
            $this->mis_data_repair || $this->mis_request_copy_data || $this->mis_grant_privilege ||
            $this->mis_others ||
            (is_array($this->services) && in_array('Software', $this->services));
    }

    /**
     * Check if has Web services
     */
    public function getWebAdminServicesAttribute()
    {
        return $this->web_new_email || $this->web_reset_email || $this->web_tarpaulin ||
            $this->web_photo_video || $this->web_website_update || $this->web_request_multimedia ||
            $this->web_others ||
            (is_array($this->services) && in_array('Web Admin', $this->services));
    }

    /**
     * Get formatted date of request (safe accessor)
     */
    public function getDateOfRequestFormattedAttribute()
    {
        if (!$this->date_of_request) {
            return 'N/A';
        }

        try {
            // If it's already a Carbon instance, use it; otherwise convert
            $date = is_string($this->date_of_request) ? \Carbon\Carbon::parse($this->date_of_request) : $this->date_of_request;
            return $date->format('M d, Y');
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    public function canBeEditedByUser(): bool
    {
        return $this->status === 'pending' && empty($this->assigned_technician_id);
    }

    public function canBeCancelledByUser(): bool
    {
        return !$this->assigned_technician_id && in_array($this->status, ['submitted', 'pending'], true);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    public function getResponsibilityLabelAttribute(): string
    {
        return match ($this->status) {
            'awaiting_requester_info', 'awaiting_confirmation' => 'Waiting for you',
            'submitted', 'pending', 'processing', 'reopened', 'escalated' => 'Waiting for MIS',
            'completed', 'closed' => 'Completed',
            'cancelled' => 'Cancelled',
            'rejected' => 'Closed by MIS',
            default => 'Status updated',
        };
    }

    public function canBeReopenedByUser(int $days = 14): bool
    {
        if (!in_array($this->status, ['awaiting_confirmation', 'completed', 'closed'], true)) {
            return false;
        }

        return !$this->closed_at || $this->closed_at->greaterThanOrEqualTo(now()->subDays($days));
    }

    public function getReopenUntilAttribute()
    {
        return $this->closed_at?->copy()->addDays(14);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->expected_completion_at || $this->status === 'awaiting_requester_info') {
            return false;
        }

        return in_array($this->status, self::OPEN_STATUSES, true)
            && $this->expected_completion_at->isPast();
    }

    public function getSlaLabelAttribute(): string
    {
        if ($this->status === 'awaiting_requester_info') {
            return 'Paused while waiting for requester';
        }

        return $this->is_overdue ? 'Overdue' : 'On schedule';
    }
}
