<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, convert any "on-hold" statuses to "processing"
        DB::table('service_requests')
            ->where('status', 'on-hold')
            ->update(['status' => 'processing']);

        DB::table('request_status_logs')
            ->where('status', 'on-hold')
            ->update(['status' => 'processing']);

        // Update the ENUM to remove "on-hold"
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('submitted', 'pending', 'processing', 'completed', 'cancelled', 'rejected') DEFAULT 'submitted'");

        DB::statement("ALTER TABLE request_status_logs MODIFY COLUMN status ENUM('submitted', 'pending', 'processing', 'completed', 'cancelled', 'rejected')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the ENUM back to include "on-hold"
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('submitted', 'pending', 'processing', 'completed', 'cancelled', 'on-hold', 'rejected') DEFAULT 'submitted'");

        DB::statement("ALTER TABLE request_status_logs MODIFY COLUMN status ENUM('submitted', 'pending', 'processing', 'completed', 'cancelled', 'on-hold', 'rejected')");
    }
};
