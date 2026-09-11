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
            if (! Schema::hasColumn('service_requests', 'assigned_technician_id')) {
                $table->foreignId('assigned_technician_id')
                    ->nullable()
                    ->constrained('technicians')
                    ->onDelete('set null')
                    ->after('assigned_to');
            }

            if (! Schema::hasColumn('service_requests', 'assigned_by')) {
                $table->foreignId('assigned_by')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null')
                    ->after('assigned_technician_id');
            }

            if (! Schema::hasColumn('service_requests', 'assigned_date')) {
                $table->timestamp('assigned_date')
                    ->nullable()
                    ->after('assigned_by');
            }

            if (! Schema::hasColumn('service_requests', 'technician_notes')) {
                $table->text('technician_notes')->nullable()->after('assigned_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['assigned_technician_id_foreign']);
            $table->dropForeignKeyIfExists(['assigned_by_foreign']);
            $table->dropColumnIfExists('assigned_technician_id');
            $table->dropColumnIfExists('assigned_by');
            $table->dropColumnIfExists('assigned_date');
            $table->dropColumnIfExists('technician_notes');
        });
    }
};
