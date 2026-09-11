<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUSES = [
        'submitted',
        'pending',
        'processing',
        'awaiting_confirmation',
        'completed',
        'closed',
        'reopened',
        'escalated',
        'cancelled',
        'rejected',
    ];

    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $statuses = "'" . implode("','", self::STATUSES) . "'";
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM({$statuses}) NOT NULL DEFAULT 'submitted'");
            DB::statement("ALTER TABLE request_status_logs MODIFY COLUMN status ENUM({$statuses}) NOT NULL");
        }

        Schema::table('service_requests', function (Blueprint $table) {
            $table->timestamp('work_completed_at')->nullable()->after('date_completed');
            $table->timestamp('resolution_confirmed_at')->nullable()->after('work_completed_at');
            $table->timestamp('closed_at')->nullable()->after('resolution_confirmed_at');
            $table->timestamp('reopened_at')->nullable()->after('closed_at');
            $table->timestamp('escalated_at')->nullable()->after('reopened_at');
            $table->text('escalation_reason')->nullable()->after('escalated_at');
            $table->string('feedback_follow_up_status')->nullable()->after('feedback_submitted_at');
            $table->text('feedback_follow_up_notes')->nullable()->after('feedback_follow_up_status');
            $table->foreignId('feedback_reviewed_by')->nullable()->after('feedback_follow_up_notes')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('feedback_reviewed_at')->nullable()->after('feedback_reviewed_by');

            $table->index(['status', 'expected_completion_at'], 'service_requests_sla_queue_index');
            $table->index(['feedback_resolved', 'feedback_follow_up_status'], 'service_requests_feedback_queue_index');
        });

        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80);
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['service_request_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex('service_requests_sla_queue_index');
            $table->dropIndex('service_requests_feedback_queue_index');
            $table->dropConstrainedForeignId('feedback_reviewed_by');
            $table->dropColumn([
                'work_completed_at',
                'resolution_confirmed_at',
                'closed_at',
                'reopened_at',
                'escalated_at',
                'escalation_reason',
                'feedback_follow_up_status',
                'feedback_follow_up_notes',
                'feedback_reviewed_at',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            $statuses = "'submitted','pending','processing','completed','cancelled','rejected'";
            DB::table('service_requests')->whereIn('status', [
                'awaiting_confirmation', 'closed', 'reopened', 'escalated',
            ])->update(['status' => 'processing']);
            DB::table('request_status_logs')->whereIn('status', [
                'awaiting_confirmation', 'closed', 'reopened', 'escalated',
            ])->update(['status' => 'processing']);
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM({$statuses}) NOT NULL DEFAULT 'submitted'");
            DB::statement("ALTER TABLE request_status_logs MODIFY COLUMN status ENUM({$statuses}) NOT NULL");
        }
    }
};
