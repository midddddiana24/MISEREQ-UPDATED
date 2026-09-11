<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_incident_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('ict_incident_reports', 'updo_director_name')) {
                $table->string('updo_director_name')->nullable()->after('noted_by_signature');
                $table->text('updo_director_signature')->nullable()->after('updo_director_name');
            }
        });

        DB::table('ict_incident_reports')
            ->where('noted_by_role', 'UPDO Director')
            ->update(['noted_by_role' => 'MIS Head']);
    }

    public function down(): void
    {
        Schema::table('ict_incident_reports', function (Blueprint $table) {
            $columns = array_filter(
                ['updo_director_name', 'updo_director_signature'],
                fn (string $column) => Schema::hasColumn('ict_incident_reports', $column)
            );
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
