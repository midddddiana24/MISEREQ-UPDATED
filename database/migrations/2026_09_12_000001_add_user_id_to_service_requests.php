<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The live database gained service_requests.user_id outside of the
     * migration history (manual ALTER). Fresh installs (CI, staging, new
     * environments) were missing the column, breaking every user-scoped
     * query and the foreign key index migration. This reconciles the drift.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('service_requests', 'user_id')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')
                    ->nullable()
                    ->after('id')
                    ->index('user_id');
            });

            Schema::table('service_requests', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_requests', 'user_id')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex('user_id');
                $table->dropColumn('user_id');
            });
        }
    }
};
