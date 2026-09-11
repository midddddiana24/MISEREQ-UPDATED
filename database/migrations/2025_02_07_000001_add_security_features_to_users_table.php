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
        Schema::table('users', function (Blueprint $table) {
            // 2FA columns
            if (!Schema::hasColumn('users', 'google2fa_secret')) {
                $table->string('google2fa_secret')->nullable()->after('password');
            }

            if (!Schema::hasColumn('users', 'two_factor_recovery_code')) {
                $table->string('two_factor_recovery_code')->nullable()->after('google2fa_secret');
            }

            if (!Schema::hasColumn('users', 'two_fa_enabled_at')) {
                $table->timestamp('two_fa_enabled_at')->nullable()->after('two_factor_recovery_code');
            }

            // Password history (for preventing reuse)
            if (!Schema::hasColumn('users', 'password_history')) {
                $table->text('password_history')->nullable()->after('two_fa_enabled_at');
            }

            // Password expiry
            if (!Schema::hasColumn('users', 'password_expires_at')) {
                $table->timestamp('password_expires_at')->nullable()->after('password_history');
            }

            // Account lockout
            if (!Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->integer('failed_login_attempts')->default(0)->after('password_expires_at');
            }

            if (!Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            }

            // Session management
            if (!Schema::hasColumn('users', 'last_activity')) {
                $table->timestamp('last_activity')->nullable()->after('locked_until');
            }

            if (!Schema::hasColumn('users', 'session_ip')) {
                $table->string('session_ip')->nullable()->after('last_activity');
            }

            if (!Schema::hasColumn('users', 'session_user_agent')) {
                $table->string('session_user_agent')->nullable()->after('session_ip');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google2fa_secret',
                'two_factor_recovery_code',
                'two_fa_enabled_at',
                'password_history',
                'password_expires_at',
                'failed_login_attempts',
                'locked_until',
                'last_activity',
                'session_ip',
                'session_user_agent',
            ]);
        });
    }
};
