<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreventiveMaintenanceChecklist extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_OPTIONS = [
        'submitted' => 'Submitted',
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
    ];

    public const LEGACY_STATUSES = ['needs_action' => 'processing'];

    public function getStageStatusAttribute(): string
    {
        return self::LEGACY_STATUSES[$this->status] ?? $this->status;
    }

    public static function storedStatusesFor(string $stage): array
    {
        return array_merge([$stage], array_keys(self::LEGACY_STATUSES, $stage, true));
    }

    protected $table = 'preventive_maintenance_checklists';

    protected $fillable = [
        'user_id',
        'control_number',
        'property_number',
        'date_inspected',
        'system_owner',
        'office_department',
        'ict_type',
        'computer_type',
        'other_ict_type',
        'other_ict_specify',
        'other_ict_checks',
        'hardware_checks',
        'software_checks',
        'info_systems_checks',
        'info_system_name',
        'findings',
        'recommendations',
        'inspected_by_name',
        'inspected_by_role',
        'inspected_by_signature',
        'noted_by_name',
        'noted_by_role',
        'noted_by_signature',
        'acknowledged_by_name',
        'acknowledged_by_role',
        'acknowledged_by_signature',
        'status',
        'archived_at',
        'archived_by',
        'archive_reason',
        'service_request_id',
        'incident_report_id',
    ];

    protected $casts = [
        'hardware_checks' => 'array',
        'software_checks' => 'array',
        'info_systems_checks' => 'array',
        'other_ict_checks' => 'array',
        'date_inspected' => 'date',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->control_number)) {
                $year = now()->format('Y');
                $month = now()->format('m');
                $count = self::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count() + 1;
                $model->control_number = sprintf('PMC-%s%s-%04d', $year, $month, $count);
            }
        });
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function incidentReport()
    {
        return $this->belongsTo(IctIncidentReport::class, 'incident_report_id');
    }
}
