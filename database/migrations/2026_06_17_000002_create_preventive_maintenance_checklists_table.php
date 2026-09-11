<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('preventive_maintenance_checklists')) {
            return;
        }

        Schema::create('preventive_maintenance_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->nullOnDelete();
            $table->string('control_number')->unique();
            $table->string('property_number', 100)->nullable();
            $table->date('date_inspected');
            $table->string('system_owner');
            $table->string('office_department')->nullable();
            $table->string('ict_type');
            $table->string('computer_type')->nullable();
            $table->string('other_ict_type', 100)->nullable();
            $table->string('other_ict_specify')->nullable();
            $table->json('hardware_checks')->nullable();
            $table->json('software_checks')->nullable();
            $table->json('info_systems_checks')->nullable();
            $table->string('info_system_name')->nullable();
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('inspected_by_name')->nullable();
            $table->string('inspected_by_role')->nullable();
            $table->text('inspected_by_signature')->nullable();
            $table->string('noted_by_name')->nullable();
            $table->string('noted_by_role')->nullable();
            $table->text('noted_by_signature')->nullable();
            $table->string('acknowledged_by_name')->nullable();
            $table->string('acknowledged_by_role')->nullable();
            $table->text('acknowledged_by_signature')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_checklists');
    }
};
