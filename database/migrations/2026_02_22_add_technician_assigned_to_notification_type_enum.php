<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the notification_type enum to include 'technician_assigned'
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('notification_type', [
                'status_update',
                'request_created',
                'request_completed',
                'request_rejected',
                'technician_assigned'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('notification_type', [
                'status_update',
                'request_created',
                'request_completed',
                'request_rejected'
            ])->change();
        });
    }
};
