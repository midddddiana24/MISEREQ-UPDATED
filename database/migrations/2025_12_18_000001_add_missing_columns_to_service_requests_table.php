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
            // Add missing columns if they don't exist
            if (! Schema::hasColumn('service_requests', 'requester_designation')) {
                $table->string('requester_designation')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'requester_office')) {
                $table->string('requester_office')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'network_services')) {
                $table->json('network_services')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'ict_equipment_services')) {
                $table->json('ict_equipment_services')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'mis_software_services')) {
                $table->json('mis_software_services')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'web_admin_services')) {
                $table->json('web_admin_services')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'property_number')) {
                $table->string('property_number')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('low');
            }
            if (! Schema::hasColumn('service_requests', 'unit_head_name')) {
                $table->string('unit_head_name')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'unit_head_phone')) {
                $table->string('unit_head_phone')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'responded_by')) {
                $table->string('responded_by')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'prr_number')) {
                $table->string('prr_number')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'date_responded')) {
                $table->dateTime('date_responded')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'major_repair')) {
                $table->boolean('major_repair')->default(false);
            }
            if (! Schema::hasColumn('service_requests', 'date_completed')) {
                $table->dateTime('date_completed')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'mis_head_signature')) {
                $table->text('mis_head_signature')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'comments')) {
                $table->text('comments')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'end_user_signature')) {
                $table->text('end_user_signature')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'acceptance_date')) {
                $table->dateTime('acceptance_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumnIfExists('requester_designation');
            $table->dropColumnIfExists('requester_office');
            $table->dropColumnIfExists('network_services');
            $table->dropColumnIfExists('ict_equipment_services');
            $table->dropColumnIfExists('mis_software_services');
            $table->dropColumnIfExists('web_admin_services');
            $table->dropColumnIfExists('property_number');
            $table->dropColumnIfExists('priority');
            $table->dropColumnIfExists('date_of_request');
            $table->dropColumnIfExists('time_of_request');
            $table->dropColumnIfExists('unit_head_name');
            $table->dropColumnIfExists('unit_head_phone');
            $table->dropColumnIfExists('responded_by');
            $table->dropColumnIfExists('prr_number');
            $table->dropColumnIfExists('date_responded');
            $table->dropColumnIfExists('major_repair');
            $table->dropColumnIfExists('date_completed');
            $table->dropColumnIfExists('mis_head_signature');
            $table->dropColumnIfExists('comments');
            $table->dropColumnIfExists('end_user_signature');
            $table->dropColumnIfExists('acceptance_date');
        });
    }
};
