<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ict_incident_reports')) {
            return;
        }

        Schema::create('ict_incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->nullOnDelete();
            $table->string('document_number')->unique();
            $table->string('reported_by');
            $table->string('id_number', 100)->nullable();
            $table->string('department')->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('reporter_phone', 50)->nullable();
            $table->text('reporter_signature')->nullable();
            $table->string('received_by')->nullable();
            $table->date('received_date')->nullable();
            $table->date('date_of_incident');
            $table->time('time_of_incident')->nullable();
            $table->json('nature_of_incident')->nullable();
            $table->string('nature_others')->nullable();
            $table->text('details_of_incident')->nullable();
            $table->text('action_plan')->nullable();
            $table->text('remarks')->nullable();
            $table->string('inspected_by_name')->nullable();
            $table->string('inspected_by_role')->nullable();
            $table->text('inspected_by_signature')->nullable();
            $table->string('noted_by_name')->nullable();
            $table->string('noted_by_role')->nullable();
            $table->text('noted_by_signature')->nullable();
            $table->string('acknowledged_by_name')->nullable();
            $table->string('acknowledged_by_role')->nullable();
            $table->text('acknowledged_by_signature')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ict_incident_reports');
    }
};
