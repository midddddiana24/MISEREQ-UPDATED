<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            // User Info
            $table->string('requestor_name');
            $table->string('department')->nullable();
            $table->string('office_location')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('phone_local_no')->nullable();
            $table->date('date_of_request')->nullable();
            $table->time('time_of_request')->nullable();
            // Network/Telephone Services
            $table->boolean('network_new_computer')->default(false);
            $table->boolean('network_new_printer')->default(false);
            $table->boolean('network_data_request')->default(false);
            $table->boolean('network_new_telephone')->default(false);
            $table->boolean('network_change_telephone')->default(false);
            $table->boolean('network_move_location')->default(false);
            $table->string('network_others')->nullable();
            // ICT Equipment Services
            $table->boolean('ict_troubleshoot_pc')->default(false);
            $table->boolean('ict_troubleshoot_printer')->default(false);
            $table->boolean('ict_troubleshoot_telephone')->default(false);
            $table->boolean('ict_hardware_install')->default(false);
            $table->boolean('ict_software_install')->default(false);
            $table->boolean('ict_procurement')->default(false);
            $table->string('ict_others')->nullable();
            $table->string('ict_property_no')->nullable();
            // MIS Software/Application Services
            $table->boolean('mis_new_user')->default(false);
            $table->boolean('mis_reset_password')->default(false);
            $table->boolean('mis_software_problem')->default(false);
            $table->boolean('mis_data_repair')->default(false);
            $table->boolean('mis_request_copy_data')->default(false);
            $table->boolean('mis_grant_privilege')->default(false);
            $table->string('mis_others')->nullable();
            // Web Administrator Services
            $table->boolean('web_new_email')->default(false);
            $table->boolean('web_reset_email')->default(false);
            $table->boolean('web_tarpaulin')->default(false);
            $table->boolean('web_photo_video')->default(false);
            $table->boolean('web_website_update')->default(false);
            $table->boolean('web_request_multimedia')->default(false);
            $table->string('web_others')->nullable();
            // Description
            $table->text('description')->nullable();
            // MIS Personnel
            $table->string('assigned_to')->nullable();
            $table->string('responded_by')->nullable();
            $table->string('prr_no')->nullable();
            $table->boolean('major_repair')->default(false);
            $table->dateTime('date_responded')->nullable();
            $table->dateTime('date_completed')->nullable();
            $table->string('mis_director_signature')->nullable();
            // End-User Acceptance
            $table->text('comments')->nullable();
            $table->string('end_user_signature')->nullable();
            $table->date('end_user_date')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('service_requests');
    }
};
