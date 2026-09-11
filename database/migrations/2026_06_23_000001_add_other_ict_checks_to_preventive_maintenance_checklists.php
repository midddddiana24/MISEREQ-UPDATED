<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('preventive_maintenance_checklists')) {
            return;
        }

        Schema::table('preventive_maintenance_checklists', function (Blueprint $table) {
            if (! Schema::hasColumn('preventive_maintenance_checklists', 'other_ict_checks')) {
                $table->json('other_ict_checks')->nullable()->after('other_ict_specify');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('preventive_maintenance_checklists')) {
            return;
        }

        Schema::table('preventive_maintenance_checklists', function (Blueprint $table) {
            if (Schema::hasColumn('preventive_maintenance_checklists', 'other_ict_checks')) {
                $table->dropColumn('other_ict_checks');
            }
        });
    }
};
