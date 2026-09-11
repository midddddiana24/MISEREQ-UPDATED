<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_incident_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('ict_incident_reports', 'requester_position')) {
                $table->string('requester_position')->nullable()->after('department');
            }
            if (!Schema::hasColumn('ict_incident_reports', 'received_time')) {
                $table->time('received_time')->nullable()->after('received_date');
            }
        });

        Schema::table('preventive_maintenance_checklists', function (Blueprint $table) {
            if (!Schema::hasColumn('preventive_maintenance_checklists', 'incident_report_id')) {
                $table->foreignId('incident_report_id')
                    ->nullable()
                    ->unique()
                    ->after('service_request_id')
                    ->constrained('ict_incident_reports')
                    ->nullOnDelete();
            }

            $table->date('date_inspected')->nullable()->change();
            $table->string('ict_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('preventive_maintenance_checklists', function (Blueprint $table) {
            if (Schema::hasColumn('preventive_maintenance_checklists', 'incident_report_id')) {
                $table->dropConstrainedForeignId('incident_report_id');
            }
        });

        Schema::table('ict_incident_reports', function (Blueprint $table) {
            $columns = array_filter(
                ['requester_position', 'received_time'],
                fn (string $column) => Schema::hasColumn('ict_incident_reports', $column)
            );
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
