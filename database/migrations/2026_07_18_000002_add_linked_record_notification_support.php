<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('action_url')->nullable()->after('message');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN notification_type ENUM('status_update','request_created','request_completed','request_rejected','technician_assigned','clarification_requested','client_update','target_date_updated','visit_scheduled','incident_report_update','maintenance_update') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('notifications')->whereIn('notification_type', ['incident_report_update', 'maintenance_update'])
                ->update(['notification_type' => 'client_update']);
            DB::statement("ALTER TABLE notifications MODIFY COLUMN notification_type ENUM('status_update','request_created','request_completed','request_rejected','technician_assigned','clarification_requested','client_update','target_date_updated','visit_scheduled') NOT NULL");
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('action_url');
        });
    }
};
