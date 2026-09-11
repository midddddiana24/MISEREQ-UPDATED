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
        Schema::table('login_attempts', function (Blueprint $table) {
            // Add device/browser information if not exists
            if (!Schema::hasColumn('login_attempts', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }

            if (!Schema::hasColumn('login_attempts', 'device_info')) {
                $table->string('device_info')->nullable()->default('Unknown Device')->after('user_agent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('login_attempts', 'user_agent')) {
                $table->dropColumn('user_agent');
            }

            if (Schema::hasColumn('login_attempts', 'device_info')) {
                $table->dropColumn('device_info');
            }
        });
    }
};
