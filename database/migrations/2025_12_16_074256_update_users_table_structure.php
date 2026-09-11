<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add columns if they don't exist
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->after('id');
            }
            if (!Schema::hasColumn('users', 'middle_initial')) {
                $table->string('middle_initial', 10)->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->after('middle_initial');
            }
            if (!Schema::hasColumn('users', 'office')) {
                $table->string('office')->after('last_name');
            }
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->after('office');
            }

            // Drop email if it exists and you're using username instead
            // if (Schema::hasColumn('users', 'email')) {
            //     $table->dropColumn('email');
            // }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_initial', 'last_name', 'office', 'username']);
        });
    }
};
