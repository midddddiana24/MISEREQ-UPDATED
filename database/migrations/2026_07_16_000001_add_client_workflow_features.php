<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('sent_at')->index();
            }
        });

        Schema::table('service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('service_requests', 'expected_completion_at')) {
                $table->timestamp('expected_completion_at')->nullable()->after('assigned_date');
            }
            if (! Schema::hasColumn('service_requests', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('comments');
            }
            if (! Schema::hasColumn('service_requests', 'feedback_rating')) {
                $table->unsignedTinyInteger('feedback_rating')->nullable()->after('cancellation_reason');
                $table->text('feedback_comment')->nullable()->after('feedback_rating');
                $table->boolean('feedback_resolved')->nullable()->after('feedback_comment');
                $table->timestamp('feedback_submitted_at')->nullable()->after('feedback_resolved');
            }
        });

        Schema::create('service_request_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('form_type')->default('service_request');
            $table->json('payload');
            $table->timestamps();
            $table->unique(['user_id', 'form_type']);
        });

        Schema::create('service_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
            $table->index(['service_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_attachments');
        Schema::dropIfExists('service_request_drafts');

        Schema::table('service_requests', function (Blueprint $table) {
            $columns = [
                'expected_completion_at', 'cancellation_reason', 'feedback_rating',
                'feedback_comment', 'feedback_resolved', 'feedback_submitted_at',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('service_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
