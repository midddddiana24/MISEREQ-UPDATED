<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'email_verified_at')) {
            DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
        }

        DB::table('users')->whereNotNull('google2fa_secret')->orderBy('id')->eachById(function ($user) {
            try {
                Crypt::decryptString($user->google2fa_secret);
            } catch (Throwable) {
                DB::table('users')->where('id', $user->id)->update([
                    'google2fa_secret' => Crypt::encryptString($user->google2fa_secret),
                ]);
            }
        });

        DB::table('users')->whereNotNull('two_factor_recovery_code')->orderBy('id')->eachById(function ($user) {
            try {
                Crypt::decryptString($user->two_factor_recovery_code);
            } catch (Throwable) {
                $codes = json_decode($user->two_factor_recovery_code, true);
                if (! is_array($codes)) {
                    $codes = array_values(array_filter(array_map('trim', explode(',', $user->two_factor_recovery_code))));
                }
                DB::table('users')->where('id', $user->id)->update([
                    'two_factor_recovery_code' => Crypt::encryptString(json_encode($codes)),
                ]);
            }
        });

        Schema::table('technicians', function (Blueprint $table) {
            if (! Schema::hasColumn('technicians', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status')->index();
                $table->timestamp('deactivated_at')->nullable()->after('is_active');
                $table->foreignId('deactivated_by')->nullable()->after('deactivated_at')->constrained('users')->nullOnDelete();
            }
        });

        foreach (['ict_incident_reports', 'preventive_maintenance_checklists'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('status');
                    $table->foreignId('archived_by')->nullable()->after('archived_at')->constrained('users')->nullOnDelete();
                    $table->text('archive_reason')->nullable()->after('archived_by');
                }
            });
        }

        foreach (['service_requests', 'service_request_attachments', 'clarification_attachments'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'path') && $tableName !== 'service_requests') {
                continue;
            }
            $pathColumn = $tableName === 'service_requests' ? 'attachment' : 'path';
            DB::table($tableName)->whereNotNull($pathColumn)->orderBy('id')->eachById(function ($record) use ($pathColumn) {
                $path = $record->{$pathColumn};
                if (! $path || ! Storage::disk('public')->exists($path)) {
                    return;
                }
                if (! Storage::disk('secure')->exists($path)) {
                    Storage::disk('secure')->put($path, Storage::disk('public')->get($path));
                }
                if (Storage::disk('secure')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            if (Schema::hasColumn('technicians', 'deactivated_by')) {
                $table->dropConstrainedForeignId('deactivated_by');
            }
            $table->dropColumn(array_values(array_filter(['is_active', 'deactivated_at'], fn ($column) => Schema::hasColumn('technicians', $column))));
        });

        foreach (['ict_incident_reports', 'preventive_maintenance_checklists'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'archived_by')) {
                    $table->dropConstrainedForeignId('archived_by');
                }
                $columns = array_values(array_filter(['archived_at', 'archive_reason'], fn ($column) => Schema::hasColumn($tableName, $column)));
                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
