<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('service_requests', 'sla_paused_at')) {
                $table->timestamp('sla_paused_at')->nullable()->after('expected_completion_change_reason');
            }
            if (! Schema::hasColumn('service_requests', 'sla_total_paused_seconds')) {
                $table->unsignedBigInteger('sla_total_paused_seconds')->default(0)->after('sla_paused_at');
            }
        });

        if (! Schema::hasTable('clarification_attachments')) {
            Schema::create('clarification_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_request_clarification_id');
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->string('path');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            $constraints = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clarification_attachments'"))
                ->pluck('CONSTRAINT_NAME');
            if (! $constraints->contains('clarification_parent_fk')) {
                DB::statement('ALTER TABLE clarification_attachments ADD CONSTRAINT clarification_parent_fk FOREIGN KEY (service_request_clarification_id) REFERENCES service_request_clarifications(id) ON DELETE CASCADE');
            }
            if (! $constraints->contains('clarification_uploader_fk')) {
                DB::statement('ALTER TABLE clarification_attachments ADD CONSTRAINT clarification_uploader_fk FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL');
            }
            $indexes = collect(DB::select('SHOW INDEX FROM clarification_attachments'))->pluck('Key_name');
            if (! $indexes->contains('clarification_attachment_parent_idx')) {
                DB::statement('ALTER TABLE clarification_attachments ADD INDEX clarification_attachment_parent_idx (service_request_clarification_id)');
            }
            DB::statement("ALTER TABLE notifications MODIFY COLUMN notification_type ENUM('status_update','request_created','request_completed','request_rejected','technician_assigned','clarification_requested','client_update','target_date_updated','visit_scheduled','incident_report_update','maintenance_update','admin_action_required') NOT NULL");
        } else {
            Schema::table('clarification_attachments', function (Blueprint $table) {
                $table->foreign('service_request_clarification_id', 'clarification_parent_fk')->references('id')->on('service_request_clarifications')->cascadeOnDelete();
                $table->foreign('uploaded_by', 'clarification_uploader_fk')->references('id')->on('users')->nullOnDelete();
                $table->index('service_request_clarification_id', 'clarification_attachment_parent_idx');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('notifications')->where('notification_type', 'admin_action_required')->update(['notification_type' => 'status_update']);
            DB::statement("ALTER TABLE notifications MODIFY COLUMN notification_type ENUM('status_update','request_created','request_completed','request_rejected','technician_assigned','clarification_requested','client_update','target_date_updated','visit_scheduled','incident_report_update','maintenance_update') NOT NULL");
        }

        Schema::dropIfExists('clarification_attachments');
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['sla_paused_at', 'sla_total_paused_seconds']);
        });
    }
};
