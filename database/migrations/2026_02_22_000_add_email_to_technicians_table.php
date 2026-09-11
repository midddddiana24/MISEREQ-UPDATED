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
        Schema::table('technicians', function (Blueprint $table) {
            // Make user_id nullable
            $table->foreignId('user_id')->nullable()->change();

            // Add email field
            if (!Schema::hasColumn('technicians', 'email')) {
                $table->string('email')->nullable()->after('user_id');
            }

            // Add first_name and last_name for independent technicians
            if (!Schema::hasColumn('technicians', 'first_name')) {
                $table->string('first_name')->nullable()->after('email');
            }

            if (!Schema::hasColumn('technicians', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            if (Schema::hasColumn('technicians', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('technicians', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('technicians', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
