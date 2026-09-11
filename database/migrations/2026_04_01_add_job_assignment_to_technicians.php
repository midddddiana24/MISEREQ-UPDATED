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
            if (! Schema::hasColumn('technicians', 'job_assignment')) {
                $table->enum('job_assignment', [
                    'Network Administrator',
                    'Technical Staff',
                    'Web Administrator',
                    'Computer Programmer',
                ])->nullable()->after('specialization');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            if (Schema::hasColumn('technicians', 'job_assignment')) {
                $table->dropColumn('job_assignment');
            }
        });
    }
};
