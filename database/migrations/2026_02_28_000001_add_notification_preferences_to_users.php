<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Notification preferences
            $table->boolean('notify_email_enabled')->default(true)->after('password_expires_at');
            $table->boolean('notify_request_reminders')->default(true)->after('notify_email_enabled');
            $table->boolean('notify_system_announcements')->default(true)->after('notify_request_reminders');
            $table->boolean('notify_request_updates')->default(true)->after('notify_system_announcements');

            // Account settings
            $table->string('theme')->default('light')->after('notify_request_updates');
            $table->string('language')->default('en')->after('theme');
            $table->boolean('email_verified')->default(false)->after('language');

            // Add failed_login_attempts if not exists
            if (! Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->integer('failed_login_attempts')->default(0)->after('email_verified');
            }

            // Add locked_until if not exists
            if (! Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notify_email_enabled',
                'notify_request_reminders',
                'notify_system_announcements',
                'notify_request_updates',
                'theme',
                'language',
                'email_verified',
            ]);

            if (Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->dropColumn('failed_login_attempts');
            }

            if (Schema::hasColumn('users', 'locked_until')) {
                $table->dropColumn('locked_until');
            }
        });
    }
};
