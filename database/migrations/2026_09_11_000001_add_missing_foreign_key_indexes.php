<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing indexes on foreign key columns.
     * These columns are frequently filtered on but lack indexes, causing full-table scans.
     *
     * Critical for performance:
     * - service_requests.user_id: filtered in every user dashboard/list query
     * - ict_incident_reports.user_id, service_request_id: filtered when loading user's records
     * - preventive_maintenance_checklists.user_id, service_request_id: same as above
     * - request_status_logs.updated_by: filtered when loading audit trail
     * - service_request_clarifications.asked_by, answered_by: filtered when loading clarifications
     * - clarification_attachments.uploaded_by: filtered when loading attachments
     */
    public function up(): void
    {
        // service_requests.user_id - PRIMARY: filtered on every user dashboard/list query
        // (column itself is added by 2026_09_12 migration on fresh installs)
        if (
            Schema::hasTable('service_requests')
            && Schema::hasColumn('service_requests', 'user_id')
            && ! $this->hasIndex('service_requests', 'user_id')
        ) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        // request_status_logs.updated_by
        if (Schema::hasTable('request_status_logs') && ! $this->hasIndex('request_status_logs', 'updated_by')) {
            Schema::table('request_status_logs', function (Blueprint $table) {
                $table->index('updated_by');
            });
        }

        // ict_incident_reports.user_id and service_request_id
        if (Schema::hasTable('ict_incident_reports')) {
            Schema::table('ict_incident_reports', function (Blueprint $table) {
                if (! $this->hasIndex('ict_incident_reports', 'user_id')) {
                    $table->index('user_id');
                }
                if (! $this->hasIndex('ict_incident_reports', 'service_request_id')) {
                    $table->index('service_request_id');
                }
            });
        }

        // preventive_maintenance_checklists.user_id and service_request_id
        if (Schema::hasTable('preventive_maintenance_checklists')) {
            Schema::table('preventive_maintenance_checklists', function (Blueprint $table) {
                if (! $this->hasIndex('preventive_maintenance_checklists', 'user_id')) {
                    $table->index('user_id');
                }
                if (! $this->hasIndex('preventive_maintenance_checklists', 'service_request_id')) {
                    $table->index('service_request_id');
                }
            });
        }

        // service_request_clarifications.asked_by, answered_by
        if (Schema::hasTable('service_request_clarifications')) {
            Schema::table('service_request_clarifications', function (Blueprint $table) {
                if (! $this->hasIndex('service_request_clarifications', 'asked_by')) {
                    $table->index('asked_by');
                }
                if (! $this->hasIndex('service_request_clarifications', 'answered_by')) {
                    $table->index('answered_by');
                }
            });
        }

        // clarification_attachments.uploaded_by
        if (Schema::hasTable('clarification_attachments') && ! $this->hasIndex('clarification_attachments', 'uploaded_by')) {
            Schema::table('clarification_attachments', function (Blueprint $table) {
                $table->index('uploaded_by');
            });
        }
    }

    public function down(): void
    {
        // Drop indexes in reverse order
        if (Schema::hasTable('clarification_attachments') && $this->hasIndex('clarification_attachments', 'uploaded_by')) {
            Schema::table('clarification_attachments', function (Blueprint $table) {
                $table->dropIndex(['uploaded_by']);
            });
        }

        if (Schema::hasTable('service_request_clarifications')) {
            Schema::table('service_request_clarifications', function (Blueprint $table) {
                if ($this->hasIndex('service_request_clarifications', 'asked_by')) {
                    $table->dropIndex(['asked_by']);
                }
                if ($this->hasIndex('service_request_clarifications', 'answered_by')) {
                    $table->dropIndex(['answered_by']);
                }
            });
        }

        if (Schema::hasTable('preventive_maintenance_checklists')) {
            Schema::table('preventive_maintenance_checklists', function (Blueprint $table) {
                if ($this->hasIndex('preventive_maintenance_checklists', 'user_id')) {
                    $table->dropIndex(['user_id']);
                }
                if ($this->hasIndex('preventive_maintenance_checklists', 'service_request_id')) {
                    $table->dropIndex(['service_request_id']);
                }
            });
        }

        if (Schema::hasTable('ict_incident_reports')) {
            Schema::table('ict_incident_reports', function (Blueprint $table) {
                if ($this->hasIndex('ict_incident_reports', 'user_id')) {
                    $table->dropIndex(['user_id']);
                }
                if ($this->hasIndex('ict_incident_reports', 'service_request_id')) {
                    $table->dropIndex(['service_request_id']);
                }
            });
        }

        if (Schema::hasTable('request_status_logs') && $this->hasIndex('request_status_logs', 'updated_by')) {
            Schema::table('request_status_logs', function (Blueprint $table) {
                $table->dropIndex(['updated_by']);
            });
        }

        if (Schema::hasTable('service_requests') && $this->hasIndex('service_requests', 'user_id')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
            });
        }
    }

    /**
     * Helper to check if an index exists on a column
     */
    private function hasIndex(string $table, string $column): bool
    {
        try {
            $indexes = DB::select("SHOW INDEXES FROM {$table} WHERE Column_name = ?", [$column]);

            return ! empty($indexes);
        } catch (Exception $e) {
            return false;
        }
    }
};
