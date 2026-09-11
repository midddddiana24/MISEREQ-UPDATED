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
        Schema::table('service_requests', function (Blueprint $table) {
            // Track who actually created the request (admin might create on behalf of user)
            if (! Schema::hasColumn('service_requests', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('user_id');
            }

            // Track if this was created by admin on behalf of someone
            if (! Schema::hasColumn('service_requests', 'is_admin_created')) {
                $table->boolean('is_admin_created')->default(false)->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'created_by')) {
                // Drop the foreign key constraint first
                $table->dropForeign('service_requests_created_by_foreign');
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('service_requests', 'is_admin_created')) {
                $table->dropColumn('is_admin_created');
            }
        });
    }
};
