<?php

// ============================================================
// FILE 1: app/Models/IctIncidentReport.php
// ============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IctIncidentReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ict_incident_reports';

    protected $fillable = [
        'document_number',
        'user_id',
        'reported_by',
        'id_number',
        'department',
        'requester_position',
        'reporter_email',
        'reporter_phone',
        'reporter_signature',
        'received_by',
        'received_date',
        'received_time',
        'date_of_incident',
        'time_of_incident',
        'nature_of_incident',
        'nature_others',
        'details_of_incident',
        'action_plan',
        'remarks',
        'inspected_by_name',
        'inspected_by_role',
        'inspected_by_signature',
        'noted_by_name',
        'noted_by_role',
        'noted_by_signature',
        'updo_director_name',
        'updo_director_signature',
        'acknowledged_by_name',
        'acknowledged_by_role',
        'acknowledged_by_signature',
        'status',
        'archived_at',
        'archived_by',
        'archive_reason',
        'service_request_id',
    ];

    protected $casts = [
        'nature_of_incident' => 'array',
        'date_of_incident' => 'date',
        'received_date' => 'date',
        'archived_at' => 'datetime',
    ];

    /** Auto-generate document number on creation */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->document_number)) {
                $year = now()->format('Y');
                $month = now()->format('m');
                $count = self::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count() + 1;
                $model->document_number = sprintf('IIR-%s%s-%04d', $year, $month, $count);
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

    public function preventiveMaintenanceChecklist()
    {
        return $this->hasOne(PreventiveMaintenanceChecklist::class, 'incident_report_id');
    }
}
