<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['service_request_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('service_request_id')->nullable()->change();
            $table->foreign('service_request_id')->references('id')->on('service_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('notifications')->whereNull('service_request_id')->delete();

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['service_request_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('service_request_id')->nullable(false)->change();
            $table->foreign('service_request_id')->references('id')->on('service_requests')->cascadeOnDelete();
        });
    }
};
