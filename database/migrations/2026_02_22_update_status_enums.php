<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add new statuses to service_requests table
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('submitted', 'pending', 'processing', 'completed', 'cancelled', 'on-hold', 'rejected') DEFAULT 'submitted'");

        // Add new statuses to request_status_logs table
        DB::statement("ALTER TABLE request_status_logs MODIFY COLUMN status ENUM('submitted', 'pending', 'processing', 'completed', 'cancelled', 'on-hold', 'rejected')");
    }

    public function down(): void
    {
        // Revert to original statuses
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('submitted', 'pending', 'processing', 'completed') DEFAULT 'submitted'");

        DB::statement("ALTER TABLE request_status_logs MODIFY COLUMN status ENUM('submitted', 'pending', 'processing', 'completed')");
    }
};
