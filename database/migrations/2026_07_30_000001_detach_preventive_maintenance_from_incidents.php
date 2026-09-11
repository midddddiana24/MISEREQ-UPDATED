<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('preventive_maintenance_checklists', 'incident_report_id')) {
            return;
        }

        // Preserve every maintenance record and its editable fields. Only the
        // automatic incident relationship is removed.
        DB::table('preventive_maintenance_checklists')
            ->whereNotNull('incident_report_id')
            ->update(['incident_report_id' => null]);
    }

    public function down(): void
    {
        // The previous incident association cannot be reconstructed reliably.
        // The maintenance records themselves are intentionally preserved.
    }
};
