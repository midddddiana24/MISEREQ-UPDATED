<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id', 120)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_name')->nullable();
            $table->timestamp('last_activity_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'revoked_at', 'last_activity_at'], 'user_sessions_active_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_security_alerts')
                ->default(true)
                ->after('notify_request_updates');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN notification_type ENUM('status_update','request_created','request_completed','request_rejected','technician_assigned','clarification_requested','client_update','target_date_updated','visit_scheduled','incident_report_update','maintenance_update','admin_action_required','security_alert') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('notifications')
                ->where('notification_type', 'security_alert')
                ->update(['notification_type' => 'client_update']);
            DB::statement("ALTER TABLE notifications MODIFY COLUMN notification_type ENUM('status_update','request_created','request_completed','request_rejected','technician_assigned','clarification_requested','client_update','target_date_updated','visit_scheduled','incident_report_update','maintenance_update','admin_action_required') NOT NULL");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notify_security_alerts');
        });

        Schema::dropIfExists('user_sessions');
    }
};
