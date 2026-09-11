<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['service_request_id', 'id'], 'messages_request_cursor_index');
            $table->index(['service_request_id', 'is_admin', 'read_at'], 'messages_request_unread_index');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_request_cursor_index');
            $table->dropIndex('messages_request_unread_index');
        });

        Schema::dropIfExists('message_attachments');
    }
};
