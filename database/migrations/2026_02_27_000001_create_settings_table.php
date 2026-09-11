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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'system_email', 'mail_host', etc.
            $table->longText('value')->nullable(); // JSON value stored as text
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('category')->default('general'); // system, email, notifications, security
            $table->text('description')->nullable();
            $table->timestamps();

            // Index for faster lookups
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
