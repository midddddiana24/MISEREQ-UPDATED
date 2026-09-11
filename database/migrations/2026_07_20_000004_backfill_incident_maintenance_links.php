<?php

use App\Models\IctIncidentReport;
use App\Services\IncidentMaintenanceWorkflowService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $workflow = app(IncidentMaintenanceWorkflowService::class);

        IctIncidentReport::query()
            ->orderBy('id')
            ->eachById(fn (IctIncidentReport $report) => $workflow->ensureChecklist($report), 100);
    }

    public function down(): void
    {
        // Linked maintenance records may already contain MIS work and are intentionally preserved.
    }
};
