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
            // Make description nullable
            if (Schema::hasColumn('service_requests', 'description')) {
                $table->text('description')->nullable()->change();
            }

            // Make phone nullable (it should already be, but ensuring)
            if (Schema::hasColumn('service_requests', 'phone')) {
                $table->string('phone')->nullable()->change();
            }

            // Make requester_designation nullable
            if (Schema::hasColumn('service_requests', 'requester_designation')) {
                $table->string('requester_designation')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // Revert changes
            $table->text('description')->change();
            $table->string('phone')->change();
            if (Schema::hasColumn('service_requests', 'requester_designation')) {
                $table->string('requester_designation')->change();
            }
        });
    }
};
