<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUSES = [
        'submitted', 'pending', 'processing', 'awaiting_requester_info',
        'awaiting_confirmation', 'completed', 'closed', 'reopened',
        'escalated', 'cancelled', 'rejected',
    ];

    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $statuses = "'" . implode("','", self::STATUSES) . "'";
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM({$statuses}) NOT NULL DEFAULT 'submitted'");
            DB::statement("ALTER TABLE request_status_logs MODIFY COLUMN status ENUM({$statuses}) NOT NULL");
            DB::statement("ALTER TABLE notifications MODIFY COLUMN notification_type ENUM('status_update','request_created','request_completed','request_rejected','technician_assigned','clarification_requested','client_update','target_date_updated','visit_scheduled') NOT NULL");
        }

        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'requested_urgency')) $table->string('requested_urgency', 20)->nullable()->after('priority');
            if (!Schema::hasColumn('service_requests', 'status_before_clarification')) $table->string('status_before_clarification', 40)->nullable()->after('status');
            if (!Schema::hasColumn('service_requests', 'client_update')) $table->text('client_update')->nullable()->after('technician_notes');
            if (!Schema::hasColumn('service_requests', 'client_update_at')) $table->timestamp('client_update_at')->nullable()->after('client_update');
            if (!Schema::hasColumn('service_requests', 'client_update_by')) $table->foreignId('client_update_by')->nullable()->after('client_update_at')->constrained('users')->nullOnDelete();
            if (!Schema::hasColumn('service_requests', 'expected_completion_change_reason')) $table->text('expected_completion_change_reason')->nullable()->after('expected_completion_at');
            if (!Schema::hasColumn('service_requests', 'scheduled_visit_at')) $table->timestamp('scheduled_visit_at')->nullable()->after('expected_completion_change_reason');
            if (!Schema::hasColumn('service_requests', 'scheduled_location')) $table->string('scheduled_location')->nullable()->after('scheduled_visit_at');
            if (!Schema::hasColumn('service_requests', 'schedule_response')) $table->string('schedule_response', 30)->nullable()->after('scheduled_location');
            if (!Schema::hasColumn('service_requests', 'schedule_response_note')) $table->text('schedule_response_note')->nullable()->after('schedule_response');
            if (!Schema::hasColumn('service_requests', 'schedule_responded_at')) $table->timestamp('schedule_responded_at')->nullable()->after('schedule_response_note');
        });

        DB::table('service_requests')
            ->whereNull('requested_urgency')
            ->update(['requested_urgency' => DB::raw('priority')]);

        if (!Schema::hasTable('service_request_clarifications')) {
            Schema::create('service_request_clarifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('asked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('question');
                $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('answer')->nullable();
                $table->timestamp('answered_at')->nullable();
                $table->timestamps();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            $index = DB::select("SHOW INDEX FROM service_request_clarifications WHERE Key_name = 'sr_clarifications_answered_idx'");
            if (empty($index)) {
                DB::statement('ALTER TABLE service_request_clarifications ADD INDEX sr_clarifications_answered_idx (service_request_id, answered_at)');
            }
        } else {
            Schema::table('service_request_clarifications', function (Blueprint $table) {
                $table->index(['service_request_id', 'answered_at'], 'sr_clarifications_answered_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_clarifications');

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_update_by');
            $table->dropColumn([
                'requested_urgency', 'status_before_clarification', 'client_update',
                'client_update_at', 'expected_completion_change_reason', 'scheduled_visit_at',
                'scheduled_location', 'schedule_response', 'schedule_response_note',
                'schedule_responded_at',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::table('notifications')->whereIn('notification_type', [
                'clarification_requested', 'client_update', 'target_date_updated', 'visit_scheduled',
            ])->update(['notification_type' => 'status_update']);
            DB::statement("ALTER TABLE notifications MODIFY COLUMN notification_type ENUM('status_update','request_created','request_completed','request_rejected','technician_assigned') NOT NULL");
            DB::table('service_requests')->where('status', 'awaiting_requester_info')->update(['status' => 'pending']);
            DB::table('request_status_logs')->where('status', 'awaiting_requester_info')->update(['status' => 'pending']);
            $statuses = "'submitted','pending','processing','awaiting_confirmation','completed','closed','reopened','escalated','cancelled','rejected'";
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM({$statuses}) NOT NULL DEFAULT 'submitted'");
            DB::statement("ALTER TABLE request_status_logs MODIFY COLUMN status ENUM({$statuses}) NOT NULL");
        }
    }
};
