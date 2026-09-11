<?php

namespace App\Services;

use App\Models\IctIncidentReport;
use App\Models\PreventiveMaintenanceChecklist;

class IncidentMaintenanceWorkflowService
{
    public function ensureChecklist(IctIncidentReport $report): PreventiveMaintenanceChecklist
    {
        $checklist = PreventiveMaintenanceChecklist::firstOrNew([
            'incident_report_id' => $report->id,
        ]);

        $this->fillSharedFields($checklist, $report, []);
        $checklist->status ??= 'pending';
        $checklist->save();

        return $checklist;
    }

    public function syncChecklist(IctIncidentReport $report, array $previous = []): PreventiveMaintenanceChecklist
    {
        $checklist = PreventiveMaintenanceChecklist::firstOrNew([
            'incident_report_id' => $report->id,
        ]);

        $this->fillSharedFields($checklist, $report, $previous);
        $checklist->status ??= 'pending';
        $checklist->save();

        return $checklist;
    }

    private function fillSharedFields(
        PreventiveMaintenanceChecklist $checklist,
        IctIncidentReport $report,
        array $previous
    ): void {
        $checklist->fill([
            'user_id' => $report->user_id,
            'service_request_id' => $report->service_request_id,
            'system_owner' => $report->reported_by,
            'office_department' => $report->department,
            'acknowledged_by_name' => $report->acknowledged_by_name ?: $report->reported_by,
            'acknowledged_by_role' => $report->acknowledged_by_role ?: $report->requester_position,
        ]);

        if (! $checklist->exists || blank($checklist->findings)
            || $this->matchesPrevious($checklist->findings, $previous['details_of_incident'] ?? null)) {
            $checklist->findings = $report->details_of_incident;
        }

        if (! $checklist->exists || blank($checklist->recommendations)
            || $this->matchesPrevious($checklist->recommendations, $previous['action_plan'] ?? null)) {
            $checklist->recommendations = $report->action_plan;
        }
    }

    private function matchesPrevious(?string $current, ?string $previous): bool
    {
        return $previous !== null && trim((string) $current) === trim($previous);
    }
}
