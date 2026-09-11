<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE technicians MODIFY COLUMN job_assignment VARCHAR(100) NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('technicians')
                ->whereNotIn('job_assignment', [
                    'Network Administrator',
                    'Technical Staff',
                    'Web Administrator',
                    'Computer Programmer',
                ])
                ->update(['job_assignment' => 'Technical Staff']);

            DB::statement("ALTER TABLE technicians MODIFY COLUMN job_assignment ENUM('Network Administrator','Technical Staff','Web Administrator','Computer Programmer') NULL");
        }
    }
};
